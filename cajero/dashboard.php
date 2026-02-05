<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cajero') {
    header("Location: ../login.php");
    exit;
}

// Marcar pedido como entregado
if (isset($_GET['entregar'])) {
    $id_pedido = intval($_GET['entregar']);
    $cajero_id = $_SESSION['usuario']['id']; // Obtenemos el ID del cajero logueado

    // Modificamos la consulta para actualizar también la columna entregado_por_cajero_id
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'entregado', entregado_por_cajero_id = ? WHERE id = ?");
    // Añadimos el nuevo parámetro (el ID del cajero) a bind_param
    $stmt->bind_param("ii", $cajero_id, $id_pedido);
    $stmt->execute();

    // Opcional: Redirigir de vuelta al dashboard para reflejar el cambio
    header("Location: dashboard.php");
    exit;
}

// Obtener todos los pedidos pendientes
$stmt = $conexion->prepare("
    SELECT p.id, u.nombre AS nombre_usuario, p.total, p.metodo_pago, p.creado_en
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.estado = 'pendiente'
    ORDER BY p.creado_en DESC
");

if ($stmt) {
    $stmt->execute();
    $pedidos = $stmt->get_result();
} else {
    die("Error al preparar consulta de pedidos: " . $conexion->error);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Cajero - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Panel Cajero - Pedidos Pendientes</h1>
    <nav>
        <a href="pedidos.php">Pedidos</a>
        <a href="quejas_sugerencias.php">Quejas/Sugerencias</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="max-width: 900px; margin: auto; padding: 20px;">
    <?php if ($pedidos->num_rows == 0): ?>
        <p>No hay pedidos pendientes.</p>
    <?php else: ?>
        <?php while ($pedido = $pedidos->fetch_assoc()): ?>
            <div style="border:1px solid #ccc; padding:15px; margin-bottom: 20px;">
                <h3>Pedido #<?php echo $pedido['id']; ?> - Cliente: <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></h3>
                <p><strong>Fecha:</strong> <?php echo $pedido['creado_en']; ?></p>
                <p><strong>Método de pago:</strong> <?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
                <h4>Detalle del pedido:</h4>
                <ul>
                    <?php
                    $stmt_det = $conexion->prepare("
                        SELECT pd.cantidad, (pd.subtotal / pd.cantidad) AS precio_unitario, pr.nombre
                        FROM detalles_pedido pd
                        JOIN productos pr ON pd.producto_id = pr.id
                        WHERE pd.pedido_id = ?
                    ");

                    $stmt_det->bind_param("i", $pedido['id']);
                    $stmt_det->execute();
                    $detalles = $stmt_det->get_result();

                    while ($detalle = $detalles->fetch_assoc()):
                    ?>
                        <li>
                            <?php echo htmlspecialchars($detalle['nombre']); ?> - Cantidad: <?php echo $detalle['cantidad']; ?> - Precio unitario: $<?php echo number_format($detalle['precio_unitario'], 2); ?>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <a href="dashboard.php?entregar=<?php echo $pedido['id']; ?>" onclick="return confirm('¿Marcar pedido como entregado?');" style="color:green;">Marcar como entregado</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</section>
</body>
</html>

<?php
// Cerrar conexión (asegurarse de que se cierra después de todas las operaciones)
if (isset($conexion)) {
    $conexion->close();
}
?>
