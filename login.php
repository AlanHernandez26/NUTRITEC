<?php
session_start();
require_once 'db/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE correo = ?");
    if (!$stmt) {
        die("Error en la consulta: " . $conexion->error);
    }
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'rol' => $usuario['rol']
            ];
            if ($usuario['rol'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($usuario['rol'] === 'cajero') {
                header("Location: cajero/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No existe usuario con ese correo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="img/nutriteclogo.png" type="image/png">
    <title>Login - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<section class="form-login">
    <?php if ($error): ?>
        <div class="alerta error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <h2>Iniciar sesión</h2>

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" required class="form-control">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required class="form-control">

        <button type="submit" class="btn">Iniciar sesión</button>

        <p>¿No tienes una cuenta? <a href="auth.php?action=register">Regístrate aquí</a></p>
    </form>
</section>


<?php include 'includes/footer.php'; ?>
</body>
</html>
