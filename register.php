<?php
session_start();
require_once 'db/conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($nombre) || empty($correo) || empty($password) || empty($password_confirm)) {
        $error = "Por favor llena todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico inválido.";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        if (!$stmt_check) {
            die("Error en la consulta: " . $conexion->error);
        }
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();

        if ($resultado->num_rows > 0) {
            $error = "El correo ya está registrado.";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, 'cliente')");
            if (!$stmt) {
                die("Error en la consulta: " . $conexion->error);
            }
            $stmt->bind_param("sss", $nombre, $correo, $hash_password);
            if ($stmt->execute()) {
                $success = "Registro exitoso. Puedes <a href='login.php'>iniciar sesión</a> ahora.";
            } else {
                $error = "Error al registrar usuario.";
            }
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="img/nutriteclogo.png" type="image/png">
    <title>Registro - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<section>
    <?php if ($error): ?>
        <div class="alerta error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alerta success"><?php echo $success; ?></div>
    <?php else: ?>
        <form method="post" action="register.php">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" required class="form-control">

            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" required class="form-control">

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required class="form-control">

            <label for="password_confirm">Confirmar contraseña</label>
            <input type="password" id="password_confirm" name="password_confirm" required class="form-control">

            <button type="submit" class="btn">Registrarse</button>
        </form>

        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
