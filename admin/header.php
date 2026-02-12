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
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../img/nutriteclogo.png" type="image/png">
    <title>Panel Admin - NutriTec</title>
        <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header class="site-header admin-header">
        <a href="../index.php" class="site-logo">
            <svg width="36" height="36" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <g fill="none" fill-rule="evenodd">
                    <path d="M32 4c-6 0-12 4-16 9-5 7-4 15 1 22 5 8 15 15 15 15s10-7 15-15c5-7 6-15 1-22C44 8 38 4 32 4z" fill="#2e7d32"/>
                    <circle cx="36" cy="22" r="6" fill="#a5d6a7"/>
                </g>
            </svg>
            <span class="site-title">NutriTec</span>
        </a>
        <h1>NutriTec - Panel Administrador</h1>
        <nav style="margin-bottom: 20px;">
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

