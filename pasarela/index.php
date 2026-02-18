<?php require_once '../paypal_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago con PayPal</title>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=MXN"></script>
</head>
<body>
    <h1>Procesar Pago</h1>
    <div id="paypal-button-container"></div>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return fetch('create_order.php', {
                    method: 'post'
                }).then(function(res) {
                    return res.json();
                }).then(function(data) {
                    return data.id;
                });
            },
            onApprove: function(data, actions) {
                return fetch('capture_order.php', {
                    method: 'post',
                    headers: {
                        'content-type': 'application/json'
                    },
                    body: JSON.stringify({
                        orderID: data.orderID
                    })
                }).then(function(res) {
                    return res.json();
                }).then(function(details) {
                    alert('Pago completado: ' + details.payer.name.given_name);
                    // Redirigir a confirmación
                    window.location.href = '../confirmacion_compra.php';
                });
            }
        }).render('#paypal-button-container');
    </script>
</body>
</html>