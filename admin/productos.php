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

$mensaje = '';


if (isset($_GET['delete'])) {
    $id_producto = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id_producto);
    if ($stmt->execute()) {
        $mensaje = "Producto eliminado con éxito.";
    } else {
        $mensaje = "Error al eliminar producto: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: productos.php");
    exit;
}



$sql_productos = "SELECT p.*, c.nombre AS nombre_categoria
                  FROM productos p
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  ORDER BY p.creado_en DESC";
$result_productos = $conn->query($sql_productos);
$productos = [];
if ($result_productos->num_rows > 0) {
    while($row = $result_productos->fetch_assoc()) {
        $productos[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos - NutriTec Admin</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { font-family: Arial, sans-serif; background: 
        header { background: linear-gradient(to right, 
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .container { width: 90%; margin: auto; padding: 20px; }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: 
            color: 
            border: 1px solid 
        }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; border-radius: 8px; overflow: hidden;}
        th, td { padding: 12px; border-bottom: 1px solid 
        th { background-color: 
        .acciones a { margin-right: 10px; text-decoration: none; }
        .acciones .edit { color: 
        .acciones .delete { color: 
        .producto-img { max-width: 50px; height: auto; }
        .btn-agregar {
             background-color: 
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
         .btn-agregar:hover {
            background-color: 
        }
    </style>
</head>
<body>
<header>
    <h1>Gestión de Productos</h1>
    <nav>
        <a href="dashboard.php">Inicio</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="container">
    <?php if ($mensaje): ?>
        <div class="message"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <h2>Listado de Productos</h2>
    
    <a href="editar_producto.php" class="btn-agregar">Agregar Nuevo Producto</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Costo</th>
                <th>Categoría</th> 
                <th>Destacado</th>
                <th>Creado En</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="9">No hay productos disponibles.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['id']); ?></td>
                        <td>
                            <?php if (!empty($producto['imagen_url'])): ?>
                                <img src="../img/<?php echo htmlspecialchars($producto['imagen_url']); ?>" alt="Imagen" class="producto-img">
                            <?php else: ?>
                                Sin imagen
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td>$<?php echo number_format($producto['costo'], 2); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre_categoria'] ?? 'Sin Categoría'); ?></td> 
                        <td><?php echo $producto['destacado'] ? 'Sí' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($producto['creado_en']); ?></td>
                        <td class="acciones">
                            
                            <a href="editar_producto.php?id=<?php echo htmlspecialchars($producto['id']); ?>" class="edit">Editar</a>
                            <a href="productos.php?delete=<?php echo htmlspecialchars($producto['id']); ?>" class="delete" onclick="return confirm('¿Estás seguro de eliminar este producto?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<footer style="text-align: center; padding: 10px; background: #e0e0e0; margin-top: 20px;">
    <p>&copy; <?php echo date("Y"); ?> NutriTec - Gestión de Productos</p>
</footer>
</body>
</html>
