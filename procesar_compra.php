<?php
session_start();
require_once 'db/conexion.php';


if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['producto_id'])) {
    echo "Producto no especificado.";
    exit;
}

$producto_id = intval($_GET['producto_id']);
$usuario_id = $_SESSION['usuario']['id'];


$sql = "SELECT costo FROM productos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Producto no encontrado.";
    $stmt->close();
    exit;
}

$producto = $result->fetch_assoc();
$costo = $producto['costo'];
$cantidad = 1; 
$subtotal = $costo * $cantidad;
$stmt->close();


$conexion->begin_transaction();

try {
    
    $registrado_por = $_SESSION['usuario']['id'];
    $estado_inicial = 'pendiente'; 
    $metodo_pago = 'efectivo'; 

    
    $sql_pedido = "INSERT INTO pedidos (usuario_id, registrado_por, total, metodo_pago, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    
    $stmt_pedido->bind_param("iidss", $usuario_id, $registrado_por, $subtotal, $metodo_pago, $estado_inicial);
    $stmt_pedido->execute();
    $pedido_id = $stmt_pedido->insert_id;
    $stmt_pedido->close();

    
    $sql_detalle = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    $stmt_detalle->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $subtotal);
    $stmt_detalle->execute();
    $stmt_detalle->close();

    
    $conexion->commit();

    header("Location: confirmacion_compra.php?pedido_id=" . urlencode($pedido_id));
    exit;

} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    echo "Error al procesar la compra: " . htmlspecialchars($e->getMessage());
}
?>
