<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cajero') {
    header("Location: ../login.php");
    exit;
}


$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'todos';


if ($estadoFiltro === 'todos') {
    $query = "
        SELECT p.id, p.usuario_id, p.total, p.metodo_pago, p.estado, p.creado_en, u.nombre AS nombre_usuario
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.creado_en DESC
    ";
    $stmt = $conexion->prepare($query);
} else {
    $query = "
        SELECT p.id, p.usuario_id, p.total, p.metodo_pago, p.estado, p.creado_en, u.nombre AS nombre_usuario
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.estado = ?
        ORDER BY p.creado_en DESC
    ";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $estadoFiltro);
}

$stmt->execute();
$pedidos = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Pedidos - Panel Cajero - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css" />
</head>
<body>
<header>
    <h1>Gestión de Pedidos</h1>
    <nav>
        <a href="dashboard.php">inicio</a>
        <a href="quejas_sugerencias.php">Quejas/Sugerencias</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="max-width: 1000px; margin: auto; padding: 20px;">
    <form method="get" action="pedidos.php" style="margin-bottom: 20px;">
        <label for="estado">Filtrar por estado:</label>
        <select name="estado" id="estado" onchange="this.form.submit()">
            <option value="todos" <?php if ($estadoFiltro === 'todos') echo 'selected'; ?>>Todos</option>
            <option value="pendiente" <?php if ($estadoFiltro === 'pendiente') echo 'selected'; ?>>Pendientes</option>
            <option value="entregado" <?php if ($estadoFiltro === 'entregado') echo 'selected'; ?>>Entregados</option>
            <option value="cancelado" <?php if ($estadoFiltro === 'cancelado') echo 'selected'; ?>>Cancelados</option>
        </select>
    </form>

    <?php if ($pedidos->num_rows == 0): ?>
        <p>No hay pedidos para mostrar.</p>
    <?php else: ?>
        <?php while ($pedido = $pedidos->fetch_assoc()): ?>
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
                <h3>Pedido 
                <p><strong>Fecha:</strong> <?php echo $pedido['creado_en']; ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($pedido['estado']); ?></p>
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
                        <li><?php echo htmlspecialchars($detalle['nombre']); ?> - Cantidad: <?php echo $detalle['cantidad']; ?> - Precio unitario: $<?php echo number_format($detalle['precio_unitario'], 2); ?></li>
                    <?php endwhile; ?>
                </ul>

                <?php if ($pedido['estado'] === 'pendiente'): ?>
                    <a href="pedidos.php?entregar=<?php echo $pedido['id']; ?>" onclick="return confirm('¿Marcar pedido como entregado?');" style="color:green;">Marcar como entregado</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</section>
</body>
</html>

<?php

if (isset($_GET['entregar'])) {
    $id_pedido = intval($_GET['entregar']);
    $stmt_upd = $conexion->prepare("UPDATE pedidos SET estado = 'entregado' WHERE id = ?");
    $stmt_upd->bind_param("i", $id_pedido);
    $stmt_upd->execute();
    header("Location: pedidos.php?estado=pendiente");
    exit;
}

$conexion->close();
?>
