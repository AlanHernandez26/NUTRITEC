<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

session_start();
require_once 'db/conexion.php';

if (!isset($_GET['pedido_id'])) {
    echo "Pedido no especificado.";
    exit;
}

$pedido_id = intval($_GET['pedido_id']);
$usuario_id = $_SESSION['usuario']['id'];

// Obtener datos del pedido
$sql = "SELECT p.id, p.total, p.metodo_pago, p.creado_en, u.nombre AS cliente
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = ? AND p.usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Pedido no encontrado.";
    exit;
}

$pedido = $resultado->fetch_assoc();

// Obtener detalles del pedido
$sql_detalles = "SELECT pr.nombre, dp.cantidad, dp.subtotal
                 FROM detalles_pedido dp
                 JOIN productos pr ON dp.producto_id = pr.id
                 WHERE dp.pedido_id = ?";
$stmt_detalle = $conexion->prepare($sql_detalles);
$stmt_detalle->bind_param("i", $pedido_id);
$stmt_detalle->execute();
$detalles = $stmt_detalle->get_result();

// Construir HTML del ticket
$html = '<h1 style="text-align:center;">NutriTec - Ticket de Compra</h1>';
$html .= '<p><strong>Cliente:</strong> ' . htmlspecialchars($pedido['cliente']) . '</p>';
$html .= '<p><strong>Pedido ID:</strong> ' . $pedido['id'] . '</p>';
$html .= '<p><strong>Fecha:</strong> ' . $pedido['creado_en'] . '</p>';
$html .= '<p><strong>Método de pago:</strong> ' . ucfirst($pedido['metodo_pago']) . '</p>';
$html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
$html .= '<thead><tr><th>Producto</th><th>Cantidad</th><th>Subtotal</th></tr></thead><tbody>';

while ($item = $detalles->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($item['nombre']) . '</td>';
    $html .= '<td>' . $item['cantidad'] . '</td>';
    $html .= '<td>$' . number_format($item['subtotal'], 2) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';
$html .= '<h3>Total: $' . number_format($pedido['total'], 2) . '</h3>';

// Crear PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("ticket_pedido_{$pedido_id}.pdf", ["Attachment" => false]);
exit;
