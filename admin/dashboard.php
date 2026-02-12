<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Panel Administrador</h1>
    <nav>
        
        <?php include 'menu.php'; ?>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="padding: 20px;">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></h2>
    <p>Aquí puedes gestionar el catálogo de productos, los platillos más pedidos e información general.</p>
    
</section>
</body>
</html>
