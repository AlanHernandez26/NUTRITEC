<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'gerente') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";
// Eliminar producto si se recibe id por GET
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt_del = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt_del->bind_param("i", $id_eliminar);
    if ($stmt_del->execute()) {
        $mensaje = "Producto eliminado correctamente.";
    } else {
        $mensaje = "Error al eliminar el producto.";
    }
}

// Obtener productos
$result = $conexion->query("SELECT * FROM productos ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Lista de Productos</h1>
    <nav>
        <a href="dashboard.php">Volver al panel</a>
        <a href="agregar_producto.php">Agregar producto</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="max-width: 900px; margin: auto; padding: 20px;">
    <?php if ($mensaje): ?>
        <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Costo</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($producto = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $producto['id']; ?></td>
                <td>
                    <?php if ($producto['imagen_url']): ?>
                        <img src="../<?php echo htmlspecialchars($producto['imagen_url']); ?>" alt="Imagen" width="80">
                    <?php else: ?>
                        Sin imagen
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                <td>$<?php echo number_format($producto['costo'], 2); ?></td>
                <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                <td>
                    <a href="editar_producto.php?id=<?php echo $producto['id']; ?>">Editar</a> |
                    <a href="productos.php?eliminar=<?php echo $producto['id']; ?>" onclick="return confirm('¿Eliminar este producto?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>
<?php include 'includes/footer.php'; ?>
</body>
</html>
