<?php
session_start();
require_once '../paypal_config.php';
require_once '../db/conexion.php';

use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

$json = file_get_contents('php://input');
$data = json_decode($json);

$orderId = $data->orderID;

$request = new OrdersCaptureRequest($orderId);

try {
    $client = getPayPalClient();
    $response = $client->execute($request);
    
    // Si captura exitosa, procesar pedido
    if ($response->result->status === 'COMPLETED') {
        $usuario_id = $_SESSION['usuario']['id'];
        $total = $_SESSION['total'];
        $productos = $_SESSION['productos'];
        
        $conexion->begin_transaction();
        
        $sql_pedido = "INSERT INTO pedidos (usuario_id, total, metodo_pago, estado) VALUES (?, ?, 'paypal', 'completado')";
        $stmt_pedido = $conexion->prepare($sql_pedido);
        $stmt_pedido->bind_param("id", $usuario_id, $total);
        $stmt_pedido->execute();
        $pedido_id = $stmt_pedido->insert_id;
        $stmt_pedido->close();
        
        $sql_detalle = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal) VALUES (?, ?, ?, ?)";
        $stmt_detalle = $conexion->prepare($sql_detalle);
        
        foreach ($productos as $prod) {
            $stmt_detalle->bind_param("iiid", $pedido_id, $prod['id'], $prod['cantidad'], $prod['subtotal']);
            $stmt_detalle->execute();
        }
        
        $stmt_detalle->close();
        $conexion->commit();
        
        unset($_SESSION['carrito']);
        unset($_SESSION['total']);
        unset($_SESSION['productos']);
        
        echo json_encode($response->result);
    } else {
        echo json_encode(['error' => 'Pago no completado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>