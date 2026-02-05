<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'gerente') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Gerente - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Panel Gerente</h1>
    <nav>
        <a href="../index.php">Inicio</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="padding: 20px;">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></h2>
    <p>Aquí puedes agregar y administrar productos.</p>
    <button onclick="location.href='agregar_producto.php'">Agregar producto</button>
    <!-- Aquí mostrarás lista de productos y opciones para editarlos -->
</section>
<?php include 'includes/footer.php'; ?>
</body>
</html>
