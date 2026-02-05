<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "nutritec");
if ($conn->connect_error) {
    die("Error en conexión: " . $conn->connect_error);
}

// --- Lógica para manejar el filtro de fecha ---
$fecha_seleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Rango horario del día seleccionado
$inicio = $fecha_seleccionada . ' 07:00:00';
$fin = $fecha_seleccionada . ' 20:00:00';

// --- Consulta SQL modificada para incluir el nombre del cajero ---
$sql = "SELECT p.id AS pedido_id, p.creado_en, u_cliente.nombre AS nombre_cliente, pr.nombre AS producto, dp.cantidad, pr.costo, dp.subtotal, p.estado, u_cajero.nombre AS nombre_cajero_entrega
        FROM pedidos p
        JOIN usuarios u_cliente ON p.usuario_id = u_cliente.id -- Unir con usuario para obtener nombre del cliente
        LEFT JOIN usuarios u_cajero ON p.entregado_por_cajero_id = u_cajero.id -- Unir con usuario para obtener nombre del cajero que entregó
        JOIN detalles_pedido dp ON dp.pedido_id = p.id
        JOIN productos pr ON dp.producto_id = pr.id
        WHERE p.estado = 'entregado' -- Filtramos solo pedidos entregados
        AND p.creado_en BETWEEN ? AND ?
        ORDER BY p.creado_en DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $inicio, $fin);
$stmt->execute();
$result = $stmt->get_result();

$ventas = [];
while ($row = $result->fetch_assoc()) {
    $ventas[] = $row;
}
$stmt->close();

// --- Exportar a Excel ---
if (isset($_GET['exportar'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ventas_entregadas_" . $fecha_seleccionada . ".xls");

    // Encabezados del Excel
    echo "ID Pedido\tFecha\tCliente\tProducto\tCantidad\tPrecio Unitario\tSubtotal\tEstado\tCajero que entregó\n";

    foreach ($ventas as $v) {
        // Usamos el nombre del cajero obtenido de la consulta
        $nombre_cajero_entrega = $v['nombre_cajero_entrega'] ? htmlspecialchars($v['nombre_cajero_entrega']) : "N/A";

        echo "{$v['pedido_id']}\t{$v['creado_en']}\t{$v['nombre_cliente']}\t{$v['producto']}\t{$v['cantidad']}\t{$v['costo']}\t{$v['subtotal']}\t{$v['estado']}\t{$nombre_cajero_entrega}\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas de Ventas Entregadas</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        header { background: linear-gradient(to right, #00704A, #1E3932); color: white; padding: 10px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .container { width: 90%; margin: auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #ccc; text-align: left; }
        .btn-exportar { background-color: #4CAF50; color: white; padding: 6px 15px; border: none; text-decoration: none; border-radius: 5px; margin-top: 10px; display: inline-block;}
        .filtro-fecha { margin-bottom: 20px; }
        .filtro-fecha label { margin-right: 10px; }
    </style>
</head>
<body>
<header>
    <h1>Estadísticas de Ventas Entregadas</h1>
    <nav>
        <a href="dashboard.php">Inicio</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="container">
    <h2>Ventas Entregadas (7:00 a.m. - 8:00 p.m.)</h2>

    <!-- Formulario de filtro por fecha -->
    <div class="filtro-fecha">
        <form method="get" action="estadisticas.php">
            <label for="fecha">Seleccionar Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($fecha_seleccionada) ?>">
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <!-- Enlace para exportar a Excel (incluye la fecha seleccionada en el enlace) -->
    <a href="estadisticas.php?exportar=1&fecha=<?= urlencode($fecha_seleccionada) ?>" class="btn-exportar">Descargar Excel</a>

    <table>
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Estado</th>
                <th>Cajero que entregó</th> <!-- Encabezado para el nombre del cajero -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $v): ?>
                <tr>
                    <td><?= $v['pedido_id'] ?></td>
                    <td><?= $v['creado_en'] ?></td>
                    <td><?= htmlspecialchars($v['nombre_cliente']) ?></td>
                    <td><?= htmlspecialchars($v['producto']) ?></td>
                    <td><?= $v['cantidad'] ?></td>
                    <td>$<?= number_format($v['costo'], 2) ?></td>
                    <td>$<?= number_format($v['subtotal'], 2) ?></td>
                    <td><?= htmlspecialchars($v['estado']) ?></td>
                    <td><?= htmlspecialchars($v['nombre_cajero_entrega'] ?? 'N/A') ?></td> <!-- Muestra el nombre del cajero o 'N/A' -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<footer style="text-align: center; padding: 10px; background: #e0e0e0; margin-top: 20px;">
    <p>&copy; <?= date("Y") ?> NutriTec - Reportes de ventas</p>
</footer>
</body>
</html>
