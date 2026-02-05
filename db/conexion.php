<?php
$host = "localhost";
$usuario = "root";
$password = "";  // Por defecto en XAMPP no hay contraseña
$base_datos = "nutritec";

// Crear conexión
$conexion = new mysqli($host, $usuario, $password, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Opcional: establecer codificación a UTF-8
$conexion->set_charset("utf8");
?>

