<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacto - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header>
    <h1>NutriTec</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="quienes_somos.php">Quiénes Somos</a>
        <a href="quejas.php">Quejas y sugerencias</a>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
        <?php endif; ?>
    </nav>
</header>

<section style="padding: 30px; max-width: 600px; margin: auto;">
    <h2>Contacto</h2>
    <p>Si tienes alguna duda o deseas comunicarte con nosotros, escríbenos a:</p>
    <ul>
        <li>Email: contacto@nutritec.com</li>
        <li>Teléfono: +52 55 1234 5678</li>
        <li>Dirección: Universidad, Edificio NutriTec, Ciudad, País</li>
    </ul>
</section>
</body>
</html>
