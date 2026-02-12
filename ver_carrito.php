<?php
session_start();
require_once 'db/conexion.php';

$carrito = $_SESSION['carrito'] ?? [];

$total = 0;
?>

<?php include 'includes/header.php'; ?>
<h2>Carrito de compras</h2>
<?php if (empty($carrito)): ?>
    <p>Tu carrito está vacío.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
            <th>Acciones</th>
        </tr>
        <?php
        foreach ($carrito as $id => $cantidad):
            $stmt = $conexion->prepare("SELECT nombre, costo FROM productos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $producto = $stmt->get_result()->fetch_assoc();
            $subtotal = $producto['costo'] * $cantidad;
            $total += $subtotal;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                <td><?php echo $cantidad; ?></td>
                <td>$<?php echo number_format($producto['costo'], 2); ?></td>
                <td>$<?php echo number_format($subtotal, 2); ?></td>
                <td><a href="carrito.php?eliminar=<?php echo $id; ?>">Eliminar</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
    <a href="checkout.php">Finalizar compra</a>
<?php endif; ?>
