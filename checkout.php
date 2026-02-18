<?php
session_start();
require_once 'db/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    echo "Tu carrito está vacío. <a href='index.php'>Volver al catálogo</a>";
    exit;
}

$total = 0;


$productos = [];
foreach ($carrito as $id => $cantidad) {
    $stmt = $conexion->prepare("SELECT id, nombre, costo FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($producto = $resultado->fetch_assoc()) {
        $subtotal = $producto['costo'] * $cantidad;
        $total += $subtotal;
        $producto['cantidad'] = $cantidad;
        $producto['subtotal'] = $subtotal;
        $productos[] = $producto;
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario']['id'];
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';

    
    if ($metodo_pago === 'tarjeta') {
        $num_tarjeta = $_POST['numero_tarjeta'] ?? '';
        $nombre_tarjeta = $_POST['nombre_tarjeta'] ?? '';
        $fecha_exp = $_POST['fecha_expiracion'] ?? '';
        $cvv = $_POST['cvv'] ?? '';

        if (!preg_match('/^\d{16}$/', $num_tarjeta) ||
            empty($nombre_tarjeta) ||
            !preg_match('/^\d{2}\/\d{2}$/', $fecha_exp) ||
            !preg_match('/^\d{3}$/', $cvv)) {
            echo "Datos de tarjeta inválidos.";
            exit;
        }
        
    } elseif ($metodo_pago === 'transferencia') {
        $referencia = trim($_POST['referencia_transferencia'] ?? '');
        if (empty($referencia)) {
            echo "Debes ingresar el número de referencia de transferencia.";
            exit;
        }
    } elseif ($metodo_pago === 'paypal') {
        $_SESSION['total'] = $total;
        $_SESSION['productos'] = $productos;
        header("Location: pasarela/index.php");
        exit;
    } else {
        $referencia = null;
    }

    $conexion->begin_transaction();

    try {
        
        
        $sql_pedido = "INSERT INTO pedidos (usuario_id, total, metodo_pago, referencia_transferencia) VALUES (?, ?, ?, ?)";
        $stmt_pedido = $conexion->prepare($sql_pedido);
        $stmt_pedido->bind_param("idss", $usuario_id, $total, $metodo_pago, $referencia);
        $stmt_pedido->execute();
        $pedido_id = $stmt_pedido->insert_id;
        $stmt_pedido->close();

        
        $sql_detalle = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal) VALUES (?, ?, ?, ?)";
        $stmt_detalle = $conexion->prepare($sql_detalle);

        foreach ($productos as $prod) {
            $stmt_detalle->bind_param("iiid", $pedido_id, $prod['id'], $prod['cantidad'], $prod['subtotal']);
            $stmt_detalle->execute();
        }

        $stmt_detalle->close();

        $conexion->commit();

        
        unset($_SESSION['carrito']);

        
        header("Location: confirmacion_compra.php?pedido_id=$pedido_id");
        exit;

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error al procesar la compra: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="img/nutriteclogo.png" type="image/png">
    <title>Checkout - NutriTec</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header>
    <h1>Checkout</h1>
</header>

<section style="max-width: 700px; margin: auto;">
    <h2>Resumen del carrito</h2>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $prod): ?>
            <tr>
                <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                <td><?php echo $prod['cantidad']; ?></td>
                <td>$<?php echo number_format($prod['costo'], 2); ?></td>
                <td>$<?php echo number_format($prod['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>

<form method="post" action="checkout.php" onsubmit="return validarPago();">
    <label for="metodo_pago">Método de pago:</label>
    <select id="metodo_pago" name="metodo_pago" required onchange="mostrarCamposPago()">
        <option value="efectivo">Efectivo</option>
        <option value="tarjeta">Tarjeta de débito/crédito</option>
        <option value="transferencia">Transferencia bancaria</option>
        <option value="paypal">PayPal</option>
    </select>

    <div id="datos_tarjeta" style="display:none; margin-top:10px;">
        <label>Número de tarjeta:</label><br>
        <input type="text" name="numero_tarjeta" maxlength="16" pattern="\d{16}" placeholder="Ej. 1234567812345678"><br>
        <label>Nombre en la tarjeta:</label><br>
        <input type="text" name="nombre_tarjeta" placeholder="Ej. Juan Perez"><br>
        <label>Fecha de expiración (MM/AA):</label><br>
        <input type="text" name="fecha_expiracion" maxlength="5" pattern="\d{2}/\d{2}" placeholder="MM/AA"><br>
        <label>CVV:</label><br>
        <input type="text" name="cvv" maxlength="3" pattern="\d{3}" placeholder="123"><br>
    </div>

    <div id="datos_transferencia" style="display:none; margin-top:10px;">
        <label>Número de referencia de transferencia:</label><br>
        <input type="text" name="referencia_transferencia" placeholder="Ej. ABC123456"><br>
    </div>

    <br>
    <button type="submit">Confirmar compra</button>
</form>
</section>

<script>
function mostrarCamposPago() {
    const metodo = document.getElementById('metodo_pago').value;
    document.getElementById('datos_tarjeta').style.display = metodo === 'tarjeta' ? 'block' : 'none';
    document.getElementById('datos_transferencia').style.display = metodo === 'transferencia' ? 'block' : 'none';
}

function validarPago() {
    const metodo = document.getElementById('metodo_pago').value;
    if (metodo === 'tarjeta') {
        const numTarjeta = document.querySelector('input[name="numero_tarjeta"]').value.trim();
        const nombreTarjeta = document.querySelector('input[name="nombre_tarjeta"]').value.trim();
        const fechaExp = document.querySelector('input[name="fecha_expiracion"]').value.trim();
        const cvv = document.querySelector('input[name="cvv"]').value.trim();

        if (!numTarjeta.match(/^\d{16}$/)) {
            alert("Ingrese un número de tarjeta válido (16 dígitos).");
            return false;
        }
        if (nombreTarjeta.length === 0) {
            alert("Ingrese el nombre en la tarjeta.");
            return false;
        }
        if (!fechaExp.match(/^\d{2}\/\d{2}$/)) {
            alert("Ingrese una fecha de expiración válida (MM/AA).");
            return false;
        }
        if (!cvv.match(/^\d{3}$/)) {
            alert("Ingrese un CVV válido (3 dígitos).");
            return false;
        }
    } else if (metodo === 'transferencia') {
        const referencia = document.querySelector('input[name="referencia_transferencia"]').value.trim();
        if (referencia.length === 0) {
            alert("Ingrese el número de referencia de la transferencia.");
            return false;
        }
    }
    return true;
}


mostrarCamposPago();
</script>
</body>
</html>
