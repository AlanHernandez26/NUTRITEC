<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'gerente') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";
if (!isset($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id = intval($_GET['id']);


$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: productos.php");
    exit;
}

$producto = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $costo = floatval($_POST['costo']);
    $categoria = $_POST['categoria'];

    $imagen_url = $producto['imagen_url'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $carpeta_subida = '../img/';
        $nombre_imagen = basename($_FILES['imagen']['name']);
        $ruta_subida = $carpeta_subida . $nombre_imagen;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_subida)) {
            $imagen_url = 'img/' . $nombre_imagen;
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    }

    if (!$mensaje) {
        $stmt_up = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, costo = ?, categoria = ?, imagen_url = ? WHERE id = ?");
        $stmt_up->bind_param("ssdssi", $nombre, $descripcion, $costo, $categoria, $imagen_url, $id);

        if ($stmt_up->execute()) {
            $mensaje = "Producto actualizado correctamente.";
            
            $producto['nombre'] = $nombre;
            $producto['descripcion'] = $descripcion;
            $producto['costo'] = $costo;
            $producto['categoria'] = $categoria;
            $producto['imagen_url'] = $imagen_url;
        } else {
            $mensaje = "Error al actualizar el producto.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Editar Producto</h1>
    <nav>
        <a href="productos.php">Volver a lista</a>
        <a href="dashboard.php">Panel</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="max-width: 600px; margin: auto; padding: 20px;">
    <?php if ($mensaje): ?>
        <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form action="editar_producto.php?id=<?php echo $producto['id']; ?>" method="POST" enctype="multipart/form-data">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="4" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea><br><br>

        <label>Costo:</label><br>
        <input type="number" name="costo" step="0.01" min="0" value="<?php echo $producto['costo']; ?>" required><br><br>

        <label>Categoría:</label><br>
        <input type="text" name="categoria" value="<?php echo htmlspecialchars($producto['categoria']); ?>" required><br><br>

        <label>Imagen actual:</label><br>
        <?php if ($producto['imagen_url']): ?>
            <img src="../<?php echo htmlspecialchars($producto['imagen_url']); ?>" alt="Imagen" width="100"><br><br>
        <?php else: ?>
            No hay imagen.<br><br>
        <?php endif; ?>

        <label>Cambiar imagen:</label><br>
        <input type="file" name="imagen" accept="image/*"><br><br>

        <button type="submit">Guardar Cambios</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>
</body>
</html>
