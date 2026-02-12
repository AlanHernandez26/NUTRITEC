<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$cantidadProductos = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $c) $cantidadProductos += (int)$c;
}
?>
<header class="site-header">
  <div class="site-logo">
    <a href="/NUTRITEC/index.php" class="site-logo-link" aria-label="NutriTec">
      <img src="img/nutriteclogo.png" alt="NutriTec" class="site-logo-img">
    </a>
    <span class="site-title">NutriTec</span>
  </div>
  <nav class="main-nav">
    <a href="index.php">Inicio</a>
    <a href="quienes_somos.php">¿Quiénes somos?</a>
    <a href="contacto.php">Contacto</a>
    <a href="quejas.php">Quejas y sugerencias</a>
    <a href="carrito.php">Carrito (<?php echo $cantidadProductos; ?>)</a>
    <?php if (isset($_SESSION['usuario'])): ?>
      <a href="logout.php">Cerrar sesión</a>
    <?php else: ?>
      <a href="login.php">Iniciar sesión</a>
    <?php endif; ?>
  </nav>
</header>
