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
    <title>Panel Admin - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>NutriTec - Panel Administrador</h1>
    <nav style="margin-bottom: 20px;">
        <a href="../index.php">Inicio</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="productos.php">Productos</a>
        <a href="platillos.php">Platillos</a>
        <a href="usuarios.php">Usuarios</a>
        <a href="reportes.php">Reportes</a>
        <a href="configuracion.php">Configuración</a>
        <a href="notificaciones.php">Notificaciones</a>
        <a href="historial.php">Historial</a>
        <a href="soporte.php">Soporte</a>
        <a href="ajustes.php">Ajustes</a>
        <a href="estadisticas.php">Estadísticas</a>
        <a href="promociones.php">Promociones</a>
        <a href="feedback.php">Feedback</a>
        <a href="faq.php">FAQ</a>
        <a href="contacto.php">Contacto</a>
        <a href="mantenimiento.php">Mantenimiento</a>
        <a href="actualizaciones.php">Actualizaciones</a>
        <a href="seguridad.php">Seguridad</a>
        <a href="privacidad.php">Privacidad</a>
        <a href="terminos.php">Términos</a>
        <a href="condiciones.php">Condiciones</a>
        <a href="acerca.php">Acerca de</a>
        <a href="ayuda.php">Ayuda</a>
        <a href="sugerencias.php">Sugerencias</a>
        <a href="../logout.php" style="color:red;">Cerrar sesión</a>
    </nav>
</header>

