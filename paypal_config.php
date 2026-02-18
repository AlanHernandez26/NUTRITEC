<?php
// Configuración de PayPal
define('PAYPAL_CLIENT_ID', 'TU_CLIENT_ID_AQUI');
define('PAYPAL_CLIENT_SECRET', 'TU_CLIENT_SECRET_AQUI');
define('PAYPAL_ENVIRONMENT', 'sandbox'); // Cambiar a 'live' para producción

// Incluir el autoload de Composer
require_once '../vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

function getPayPalClient() {
    $clientId = PAYPAL_CLIENT_ID;
    $clientSecret = PAYPAL_CLIENT_SECRET;

    if (PAYPAL_ENVIRONMENT === 'sandbox') {
        $environment = new SandboxEnvironment($clientId, $clientSecret);
    } else {
        $environment = new ProductionEnvironment($clientId, $clientSecret);
    }

    return new PayPalHttpClient($environment);
}
?>