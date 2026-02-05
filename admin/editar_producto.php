<?php
session_start();
require_once '../db/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- Obtener lista de categorías ---
$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias->num_rows > 0) {
    while($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

$editando = false;
// Inicializar variables con valores por defecto o vacíos
$id = $nombre = $descripcion = $imagen_url = ''; // imagen_url ahora será la URL
$costo = 0;
$categoria_id = null;
$destacado = 0;
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Modo edición
    $editando = true;
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 1) {
        $producto = $resultado->fetch_assoc();
        // Extraer datos del producto, incluyendo imagen_url
        $id = $producto['id'];
        $nombre = $producto['nombre'];
        $descripcion = $producto['descripcion'];
        $costo = $producto['costo'];
        $categoria_id = $producto['categoria_id'];
        $imagen_url = $producto['imagen_url']; // Obtenemos la URL de la base de datos
        $destacado = $producto['destacado'];

    } else {
        $mensaje = "Producto no encontrado.";
    }
    $stmt->close(); // Cerrar statement GET
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $costo = $_POST['costo'];
    $categoria_id = $_POST['categoria_id'];
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $imagen_url = trim($_POST['imagen_url']); // <-- Obtenemos la URL del campo de texto

    // Ya no necesitamos la lógica de manejo de $_FILES

    if ($id) {
        // Actualizar producto existente
        $stmt = $conexion->prepare("UPDATE productos SET nombre=?, descripcion=?, costo=?, categoria_id=?, imagen_url=?, destacado=? WHERE id=?");
        // Tipos: s (nombre), s (descripcion), d (costo), i (categoria_id), s (imagen_url), i (destacado), i (id)
        $stmt->bind_param("ssdisii", $nombre, $descripcion, $costo, $categoria_id, $imagen_url, $destacado, $id);

        if ($stmt->execute()) {
             // Redirigir después de actualizar
            header("Location: productos.php");
            exit;
        } else {
            $mensaje = "Error al actualizar producto: " . $stmt->error;
        }
        $stmt->close();

    } else {
        // Insertar nuevo producto
        // Validar que la URL de la imagen no esté vacía si es requerida
        if (empty($imagen_url)) {
             $mensaje = "Debe ingresar la URL de la imagen para el nuevo producto.";
        } else {
            $creado_por = $_SESSION['usuario']['id'];
            $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, costo, categoria_id, imagen_url, destacado, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?)");
            // Tipos: s (nombre), s (descripcion), d (costo), i (categoria_id), s (imagen_url), i (destacado), i (creado_por)
            $stmt->bind_param("ssdisii", $nombre, $descripcion, $costo, $categoria_id, $imagen_url, $destacado, $creado_por);

            if ($stmt->execute()) {
                 // Redirigir después de agregar
                header("Location: productos.php");
                exit;
            } else {
                 $mensaje = "Error al agregar producto: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Si hubo un error y no se redirigió, mostrar mensaje
     if (!empty($mensaje)) {
         echo "<p style='color: red;'>Error: " . htmlspecialchars($mensaje) . "</p>";
     }
}

// Cerrar conexión si no se redirigió
if (!headers_sent() && isset($conexion)) {
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $editando ? 'Editar' : 'Agregar'; ?> Producto</title>
    <link rel="stylesheet" href="../css/estilos.css">
     <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        header { background: linear-gradient(to right, #00704A, #1E3932); color: white; padding: 10px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        section { max-width: 600px; margin: auto; padding: 20px; background: white; border-radius: 8px; margin-top: 20px;}
        section h1 { margin-top: 0; }
        section label { display: block; margin-bottom: 5px; font-weight: bold; }
        section input[type="text"],
        section textarea,
        section input[type="number"],
        section select {
            width: calc(100% - 22px); /* Ajuste para padding y borde */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
         section input[type="file"] { /* Mantener estilo aunque no se use type="file" */
             margin-bottom: 15px;
         }
        section button {
            background-color: #00704A;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        section button:hover {
            background-color: #1E3932;
        }
         .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
         .producto-img-preview { max-width: 100px; height: auto; margin-top: 10px; display: block;}
    </style>
</head>
<body>
<header>
    <h1><?php echo $editando ? 'Editar' : 'Agregar'; ?> Producto</h1>
    <nav>
        <a href="dashboard.php">Inicio</a>
        <a href="productos.php">Productos</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<section>
    <?php if ($mensaje && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="message"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <!-- Eliminamos enctype="multipart/form-data" ya que no subimos archivos -->
    <form method="post" action="editar_producto.php">
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <!-- Ya no necesitamos el campo oculto para la imagen actual -->
        <?php endif; ?>

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($descripcion); ?></textarea>

        <label for="costo">Costo:</label>
        <input type="number" step="0.01" id="costo" name="costo" value="<?php echo htmlspecialchars($costo); ?>" required>

        <!-- Campo de selección de Categoría -->
        <label for="categoria_id">Categoría:</label>
        <select id="categoria_id" name="categoria_id" required>
            <option value="">-- Seleccione una categoría --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['id']); ?>"
                    <?php echo ($categoria_id == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Campo de texto para la URL de la Imagen -->
        <label for="imagen_url">URL de la Imagen:</label>
        <input type="text" id="imagen_url" name="imagen_url" value="<?php echo htmlspecialchars($imagen_url); ?>">
         <?php if ($editando && !empty($imagen_url)): ?>
            <p>Imagen actual:</p>
            <!-- Mostramos la imagen usando la URL ingresada -->
            <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="Imagen del producto" class="producto-img-preview">
        <?php endif; ?>


        <label>
            <input type="checkbox" name="destacado" <?php echo $destacado ? 'checked' : ''; ?>> Destacado
        </label>
        <br><br>

        <button type="submit"><?php echo $editando ? 'Actualizar' : 'Agregar'; ?> Producto</button>
    </form>
</section>
</body>
</html>
