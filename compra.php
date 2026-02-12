<?php
session_start();
require_once 'db/conexion.php';


if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}


if (!isset($_POST['producto_id'])) {
    echo "Producto no especificado.";
    exit;
}

$producto_id = intval($_POST['producto_id']);


$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    echo "Producto no encontrado.";
    exit;
}

$producto = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar compra - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <header>
        <h1>Confirmar compra</h1>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
    </header>

    <section style="max-width: 600px; margin: auto;">
        <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
        <img src="<?php echo $producto['imagen_url'] ?: 'img/default.jpg'; ?>" alt="Imagen del producto" style="max-width: 100%;">
        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
        <p><strong>Precio: $<?php echo number_format($producto['costo'], 2); ?></strong></p>

        <form action="procesar_compra.php" method="post">
            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
            <button type="submit">Confirmar compra</button>
        </form>
    </section>
</body>
</html>
