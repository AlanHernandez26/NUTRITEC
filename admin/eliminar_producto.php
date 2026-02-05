<?php
session_start();
require_once '../db/conexion.php';

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Verificar que se haya recibido un ID de producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: productos.php?error=ID inválido");
    exit;
}

$id = intval($_GET['id']);

// Eliminar el producto de la base de datos
$stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: productos.php?mensaje=Producto eliminado correctamente");
} else {
    header("Location: productos.php?error=Error al eliminar el producto");
}
$stmt->close();
?>
