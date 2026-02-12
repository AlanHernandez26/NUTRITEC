<?php
session_start();
require_once '../db/conexion.php';


if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: productos.php?error=ID inválido");
    exit;
}

$id = intval($_GET['id']);


$stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: productos.php?mensaje=Producto eliminado correctamente");
} else {
    header("Location: productos.php?error=Error al eliminar el producto");
}
$stmt->close();
?>
