<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Quiénes Somos - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header>
    <h1>NutriTec</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="contacto.php">Contacto</a>
        <a href="quejas.php">Quejas y sugerencias</a>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
        <?php endif; ?>
    </nav>
</header>

<section style="padding: 30px; max-width: 800px; margin: auto;">
    <h2>Quiénes Somos</h2>
    <p>
        En NutriTec, somos una plataforma dedicada a ofrecer opciones de comida saludable para toda la comunidad universitaria. 
        Nuestra misión es promover hábitos alimenticios sanos, brindando productos frescos y deliciosos, preparados con ingredientes naturales y nutritivos.
    </p>
    <p>
        Contamos con un equipo comprometido que trabaja día a día para garantizar la calidad y variedad de nuestros platillos, además de un sistema eficiente para la gestión de pedidos.
    </p>
</section>
</body>
</html>
