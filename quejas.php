<?php
session_start();
require_once 'db/conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['usuario'])) {
        $usuario_id = $_SESSION['usuario']['id'];
        $tipo = $_POST['tipo'];
        $mensaje_texto = $_POST['mensaje'];

        $stmt = $conexion->prepare("INSERT INTO quejas_sugerencias (usuario_id, tipo, mensaje) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $usuario_id, $tipo, $mensaje_texto);

        if ($stmt->execute()) {
            $mensaje = "Gracias por enviarnos tu " . ($tipo == 'queja' ? "queja" : "sugerencia") . ".";
        } else {
            $mensaje = "Error al enviar, intenta de nuevo.";
        }
    } else {
        $mensaje = "Debes iniciar sesión para enviar una queja o sugerencia.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quejas y Sugerencias - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header>
    <h1>NutriTec</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="quienes_somos.php">Quiénes Somos</a>
        <a href="contacto.php">Contacto</a>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
        <?php endif; ?>
    </nav>
</header>

<section style="padding: 30px; max-width: 600px; margin: auto;">
    <h2>Quejas y Sugerencias</h2>

    <?php if ($mensaje): ?>
        <p style="color: green;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['usuario'])): ?>
    <form method="POST" action="quejas.php">
        <label>Tipo:</label><br>
        <select name="tipo" required>
            <option value="queja">Queja</option>
            <option value="sugerencia">Sugerencia</option>
        </select><br><br>

        <label>Mensaje:</label><br>
        <textarea name="mensaje" rows="5" required></textarea><br><br>

        <button type="submit">Enviar</button>
    </form>
    <?php else: ?>
        <p>Debes <a href="login.php">iniciar sesión</a> para enviar una queja o sugerencia.</p>
    <?php endif; ?>
</section>
</body>
</html>
