<?php
require_once '../paypal_config.php';

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

$request = new OrdersCreateRequest();
$request->prefer('return=representation');

// Obtener el total del carrito. Asumir que está en sesión o calcular.
session_start();
$total = isset($_SESSION['total']) ? $_SESSION['total'] : 100.00; // Ejemplo, cambiar según tu lógica

$request->body = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "amount" => [
            "value" => number_format($total, 2, '.', ''),
            "currency_code" => "MXN"
        ]
    ]],
    "application_context" => [
        "cancel_url" => "http://localhost/NUTRITEC/cancel.php",
        "return_url" => "http://localhost/NUTRITEC/success.php"
    ]
];

try {
    $client = getPayPalClient();
    $response = $client->execute($request);
    echo json_encode($response->result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>