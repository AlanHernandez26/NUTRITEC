<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cajero') {
    header("Location: ../login.php");
    exit;
}

// Obtener quejas y sugerencias con usuario (si existe)
$query = "
    SELECT qs.id, qs.tipo, qs.mensaje, qs.creado_en, u.nombre AS nombre_usuario
    FROM quejas_sugerencias qs
    LEFT JOIN usuarios u ON qs.usuario_id = u.id
    ORDER BY qs.creado_en DESC
";
$result = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Quejas y Sugerencias - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Quejas y Sugerencias</h1>
    <nav>
        <a href="dashboard.php">inicio</a>
        <a href="pedidos.php">Pedidos</a>
        <a href="quejas_sugerencias.php">Quejas/Sugerencias</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="max-width: 900px; margin: auto; padding: 20px;">
    <?php if ($result->num_rows == 0): ?>
        <p>No hay quejas o sugerencias registradas.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Mensaje</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($row['mensaje']); ?></td>
                    <td><?php echo $row['nombre_usuario'] ? htmlspecialchars($row['nombre_usuario']) : 'Anónimo'; ?></td>
                    <td><?php echo $row['creado_en']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
</body>
</html>
