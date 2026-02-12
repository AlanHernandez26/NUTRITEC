<?php
session_start();
require_once 'db/conexion.php';

$error = '';
$success = '';
$action = $_GET['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tipo']) && $_POST['tipo'] === 'login') {
        
        $correo = trim($_POST['correo']);
        $password = $_POST['password'];

        $stmt = $conexion->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE correo = ?");
        if (!$stmt) die("Error en la consulta: " . $conexion->error);
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
    } elseif (isset($_POST['tipo']) && $_POST['tipo'] === 'register') {
        
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
            if (!$stmt_check) die("Error en la consulta: " . $conexion->error);
            $stmt_check->bind_param("s", $correo);
            $stmt_check->execute();
            $resultado = $stmt_check->get_result();

            if ($resultado->num_rows > 0) {
                $error = "El correo ya está registrado.";
            } else {
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, 'cliente')");
                if (!$stmt) die("Error en la consulta: " . $conexion->error);
                $stmt->bind_param("sss", $nombre, $correo, $hash_password);
                if ($stmt->execute()) {
                    $success = "Registro exitoso. Puedes <a href='auth.php?action=login'>iniciar sesión</a> ahora.";
                    $action = 'login';
                } else {
                    $error = "Error al registrar usuario.";
                }
            }
            $stmt_check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $action === 'register' ? 'Registro' : 'Login' ?> - NutriTec</title>
  <link rel="stylesheet" href="css/estilos.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>

<section style="max-width: 400px; margin: auto; padding: 20px;">
  <?php if ($error): ?>
    <div class="alerta error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alerta success"><?= $success ?></div>
  <?php endif; ?>

  <?php if ($action === 'register'): ?>
    <form method="post" action="auth.php?action=register">
      <input type="hidden" name="tipo" value="register" />
      <input type="text" name="nombre" placeholder="Nombre completo" required />
      <input type="email" name="correo" placeholder="Correo electrónico" required />
      <input type="password" name="password" placeholder="Contraseña" required />
      <input type="password" name="password_confirm" placeholder="Confirmar contraseña" required />
      <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes una cuenta? <a href="auth.php?action=login">Iniciar sesión</a></p>

  <?php else: ?>
    <form method="post" action="auth.php?action=login">
      <input type="hidden" name="tipo" value="login" />
      <input type="email" name="correo" placeholder="Correo electrónico" required />
      <input type="password" name="password" placeholder="Contraseña" required />
      <button type="submit">Iniciar sesión</button>
    </form>
    <p>¿No tienes una cuenta? <a href="auth.php?action=register">Regístrate aquí</a></p>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
