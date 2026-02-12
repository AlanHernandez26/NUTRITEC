<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'gerente') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $costo = floatval($_POST['costo']);
    $categoria = $_POST['categoria'];

    
    $imagen_url = null;
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
        
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, costo, categoria, imagen_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $nombre, $descripcion, $costo, $categoria, $imagen_url);

        if ($stmt->execute()) {
            $mensaje = "Producto agregado correctamente.";
        } else {
            $mensaje = "Error al guardar el producto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto - NutriTec</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<header>
    <h1>Agregar Producto</h1>
    <nav>
        <a href="dashboard.php">Volver al panel</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section style="max-width: 600px; margin: auto; padding: 20px;">
    <?php if ($mensaje): ?>
        <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form action="agregar_producto.php" method="POST" enctype="multipart/form-data">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="4" required></textarea><br><br>

        <label>Costo:</label><br>
        <input type="number" name="costo" step="0.01" min="0" required><br><br>

        <label>Categoría:</label><br>
        <input type="text" name="categoria" required><br><br>

        <label>Imagen:</label><br>
        <input type="file" name="imagen" accept="image/*"><br><br>

        <button type="submit">Agregar Producto</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>
</body>
</html>
