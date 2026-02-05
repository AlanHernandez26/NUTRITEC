<?php
session_start();
require_once 'db/conexion.php';

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$mensaje = '';

// --- Lógica para agregar/actualizar cantidad en el carrito (desde index.php fetch o formulario) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id']) && isset($_POST['cantidad'])) {
    $producto_id = intval($_POST['producto_id']);
    $cantidad = intval($_POST['cantidad']);

    // Validar que el producto exista en la base de datos antes de agregarlo/actualizarlo
    $stmt_check = $conexion->prepare("SELECT id FROM productos WHERE id = ?");
    $stmt_check->bind_param("i", $producto_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        if ($cantidad > 0) {
            $_SESSION['carrito'][$producto_id] = $cantidad;
            $mensaje = "Carrito actualizado.";
            $success = true;
        } elseif ($cantidad === 0) {
            unset($_SESSION['carrito'][$producto_id]);
            $mensaje = "Producto eliminado del carrito.";
            $success = true;
        } else {
             $mensaje = "Cantidad inválida.";
             $success = false;
        }
    } else {
        $mensaje = "Producto no válido.";
        $success = false;
    }
    $stmt_check->close();


    // Si la solicitud es AJAX (como la de index.php), responder con JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $mensaje]);
        exit;
    } else {
        // Si es una solicitud de formulario normal, redirigir
        header("Location: carrito.php");
        exit;
    }
}

// --- Lógica para eliminar un producto del carrito (desde enlace GET) ---
if (isset($_GET['eliminar'])) {
    $producto_id = intval($_GET['eliminar']);
    if (isset($_SESSION['carrito'][$producto_id])) {
        unset($_SESSION['carrito'][$producto_id]);
        $mensaje = "Producto eliminado del carrito.";
    }
     // Redirigir para evitar re-eliminación al recargar
     header("Location: carrito.php");
     exit;
}


// --- Obtener detalles de los productos en el carrito ---
$productos_en_carrito = [];
$total_carrito = 0;

if (!empty($_SESSION['carrito'])) {
    $ids_productos = array_keys($_SESSION['carrito']);
    // Crear un string de placeholders para la consulta preparada (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($ids_productos), '?'));

    // Consulta original sin unir con categorias
    $sql = "SELECT p.*
            FROM productos p
            WHERE p.id IN ($placeholders)";

    $stmt = $conexion->prepare($sql);

    // Crear string de tipos para bind_param (todos son 'i' para INT)
    $types = str_repeat('i', count($ids_productos));
    $stmt->bind_param($types, ...$ids_productos);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($producto = $resultado->fetch_assoc()) {
        $producto_id = $producto['id'];
        // Asegurarse de que el producto todavía está en el carrito (podría haber sido eliminado por otra pestaña, aunque raro)
        if (isset($_SESSION['carrito'][$producto_id])) {
             $cantidad = $_SESSION['carrito'][$producto_id];
             $subtotal = $producto['costo'] * $cantidad;
             $total_carrito += $subtotal;

             $productos_en_carrito[] = [
                 'id' => $producto['id'],
                 'nombre' => $producto['nombre'],
                 'descripcion' => $producto['descripcion'],
                 'costo' => $producto['costo'],
                 'imagen_url' => $producto['imagen_url'],
                 // 'nombre_categoria' ya no se obtiene ni se guarda aquí
                 'cantidad' => $cantidad,
                 'subtotal' => $subtotal
             ];
        }
    }
    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        header { background: linear-gradient(to right, #00704A, #1E3932); color: white; padding: 10px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .container { width: 90%; margin: auto; padding: 20px; }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; border-radius: 8px; overflow: hidden;}
        th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        .producto-img-carrito { max-width: 80px; height: auto; }
        .acciones-carrito a { color: #d9534f; text-decoration: none; }
        .total-carrito { text-align: right; font-size: 1.2em; margin-top: 20px; }
        .btn-checkout {
            display: inline-block;
            background-color: #00704A;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            background-color: #1E3932;
        }
    </style>
</head>
<body>
<header>
    <h1>Carrito de Compras</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
            <a href="register.php">Registrarse</a>
        <?php endif; ?>
         <a href="carrito.php">Carrito</a>
    </nav>
</header>

<main class="container">
    <?php if ($mensaje): ?>
        <div class="message"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if (empty($_SESSION['carrito'])): ?> <!-- Verificar directamente $_SESSION['carrito'] -->
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <!-- Eliminada columna Categoría -->
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos_en_carrito as $item): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($item['imagen_url']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" class="producto-img-carrito"></td>
                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                        <!-- Eliminada celda para Categoría -->
                        <td>$<?php echo number_format($item['costo'], 2); ?></td>
                        <td>
                            <!-- Formulario para actualizar cantidad -->
                            <form action="carrito.php" method="post" style="display:inline;">
                                <input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <input type="number" name="cantidad" value="<?php echo htmlspecialchars($item['cantidad']); ?>" min="0" style="width: 50px;">
                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        <td class="acciones-carrito">
                            <a href="carrito.php?eliminar=<?php echo htmlspecialchars($item['id']); ?>" onclick="return confirm('¿Eliminar este producto del carrito?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-carrito">
            <strong>Total del Carrito:</strong> $<?php echo number_format($total_carrito, 2); ?>
        </div>

        <a href="checkout.php" class="btn-checkout">Proceder al Pago</a> <!-- Asegúrate de que checkout.php existe -->

    <?php endif; ?>
</main>

<footer style="text-align: center; padding: 10px; background: #e0e0e0; margin-top: 20px;">
    <p>&copy; <?php echo date("Y"); ?> NutriTec - Carrito</p>
</footer>
</body>
</html>
