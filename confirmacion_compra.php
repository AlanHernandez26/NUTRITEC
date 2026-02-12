<?php
session_start();
require_once 'db/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['pedido_id'])) {
    echo "Pedido no especificado.";
    exit;
}

$pedido_id = intval($_GET['pedido_id']);


$sql = "SELECT p.id, p.total, p.metodo_pago, p.creado_en
        FROM pedidos p
        WHERE p.id = ? AND p.usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $pedido_id, $_SESSION['usuario']['id']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "No se encontró el pedido.";
    $stmt->close();
    exit;
}

$pedido = $resultado->fetch_assoc();
$stmt->close();


$sql_detalles = "SELECT dp.cantidad, dp.subtotal, pr.nombre
                 FROM detalles_pedido dp
                 JOIN productos pr ON dp.producto_id = pr.id
                 WHERE dp.pedido_id = ?";
$stmt_detalle = $conexion->prepare($sql_detalles);
$stmt_detalle->bind_param("i", $pedido_id);
$stmt_detalle->execute();
$detalles = $stmt_detalle->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Compra</title>
    <link rel="stylesheet" href="css/estilos.css">
        <link rel="icon" href="img/nutriteclogo.png" type="image/png">
        <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<header>
    <h1>Compra realizada con éxito</h1>
</header>

<section style="max-width: 600px; margin: auto; padding: 20px;">
    <h2>Detalles del pedido 
    <p><strong>Fecha:</strong> <?php echo date("d/m/Y H:i", strtotime($pedido['creado_en'])); ?></p>
    <p><strong>Método de pago:</strong> <?php echo ucfirst(htmlspecialchars($pedido['metodo_pago'])); ?></p>
    <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>

    <h3>Productos:</h3>
    <ul>
        <?php while ($detalle = $detalles->fetch_assoc()): ?>
            <li>
                <?php echo htmlspecialchars($detalle['nombre']); ?> 
                (Cantidad: <?php echo $detalle['cantidad']; ?>,
                Precio unitario: $<?php echo number_format($detalle['subtotal'] / $detalle['cantidad'], 2); ?>,
                Subtotal: $<?php echo number_format($detalle['subtotal'], 2); ?>)
            </li>
        <?php endwhile; ?>
    </ul>

    <p style="margin-top: 20px;">
        <a href="index.php">Volver al catálogo</a>
        <a href="generar_ticket.php?pedido_id=<?php echo $pedido['id']; ?>" target="_blank">📄 Descargar ticket en PDF</a>
    </p>
</section>
</body>
</html>
