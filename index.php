<?php
session_start();
require_once 'db/conexion.php';

// --- Obtener lista de categorías ---
$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias->num_rows > 0) {
    while($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// --- Lógica para filtrar productos por categoría ---
$categoria_seleccionada_id = null;
$filtro_sql = "";
$bind_params = [];
$bind_types = "";

if (isset($_GET['categoria_id']) && is_numeric($_GET['categoria_id'])) {
    $categoria_seleccionada_id = intval($_GET['categoria_id']);
    // Añadimos la condición WHERE para filtrar por categoria_id
    $filtro_sql = " WHERE p.categoria_id = ?";
    $bind_params[] = $categoria_seleccionada_id;
    $bind_types .= "i"; // 'i' para entero
}

// Calcular la cantidad total de productos en el carrito (lógica original)
$cantidadProductos = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $cantidad) {
        $cantidadProductos += $cantidad;
    }
}

// Consultar productos desde la base de datos (modificada para incluir filtro y join categoria)
$sql = "SELECT p.*, c.nombre AS nombre_categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        " . $filtro_sql . " -- Insertamos el filtro aquí
        ORDER BY p.creado_en DESC";

$stmt = $conexion->prepare($sql);

// Si hay parámetros de filtro, los enlazamos
if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

$stmt->execute();
$resultado = $stmt->get_result();

// No cerramos la conexión aquí si otros archivos la necesitan, pero en un script standalone como este, es buena práctica.
// $conexion->close(); // Comentado por si db/conexion.php maneja la conexión globalmente
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-VXX6YV8DXP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-VXX6YV8DXP');
</script>
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MJ5537P2');</script>
<!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <title>NutriTec - Comida Saludable</title>
    <link rel="stylesheet" href="css/estilos.css">
     <style>
        /* Estilos básicos para las categorías */
        .categorias-nav {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f2f2f2;
            border-radius: 5px;
        }
        .categorias-nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .categorias-nav a:hover {
            color: #00704A;
        }
         .categorias-nav a.active {
            color: #00704A;
            text-decoration: underline;
        }
         /* Asegúrate de que tus estilos.css definen .contenedor-productos y .producto */
         /* Si no, puedes añadir estilos básicos aquí */
         .contenedor-productos {
             display: grid;
             grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
             gap: 20px;
         }
         .producto {
             border: 1px solid #ccc;
             padding: 15px;
             text-align: center;
             border-radius: 8px;
             background-color: white;
         }
         .producto img {
             max-width: 100%;
             height: 150px; /* Altura fija para consistencia */
             object-fit: cover;
             margin-bottom: 10px;
             border-radius: 4px;
         }
          .producto h3 {
             margin-top: 0;
             font-size: 1.2em;
             height: 3em; /* Altura fija para nombres largos */
             overflow: hidden;
             text-overflow: ellipsis;
         }
          .producto p {
              font-size: 0.9em;
              color: #555;
              height: 4em; /* Altura fija para descripción */
              overflow: hidden;
              text-overflow: ellipsis;
          }
         .producto strong {
             font-size: 1.1em;
             color: #00704A;
             margin: 10px 0;
             display: block;
         }
          .producto form {
              display: inline-block;
              margin: 5px;
          }
          .producto button {
             background-color: #00704A;
             color: white;
             padding: 8px 15px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
          }
          .producto button:hover {
             background-color: #1E3932;
          }
     </style>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJ5537P2"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <header>
    <h1>Bienvenido a NutriTec</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="quienes_somos.php">¿Quiénes somos?</a>
        <a href="contacto.php">Contacto</a>
        <a href="quejas.php">Quejas y sugerencias</a>
        <a href="carrito.php">Ver carrito (<?php echo $cantidadProductos; ?>)</a>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
        <?php endif; ?>
    </nav>
</header>

    <main class="container"> <!-- Añadido contenedor principal -->
        <h2>Nuestros Productos</h2>

        <!-- Navegación de Categorías -->
        <div class="categorias-nav">
            <a href="index.php" class="<?php echo is_null($categoria_seleccionada_id) ? 'active' : ''; ?>">Todas las Categorías</a>
            <?php foreach ($categorias as $cat): ?>
                <a href="index.php?categoria_id=<?php echo htmlspecialchars($cat['id']); ?>"
                   class="<?php echo ($categoria_seleccionada_id == $cat['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="contenedor-productos">
            <?php while($producto = $resultado->fetch_assoc()): ?>
                <div class="producto">
                    <img src="<?php echo htmlspecialchars($producto['imagen_url'] ?: 'img/default.jpg'); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                    <p><strong>$<?php echo number_format($producto['costo'], 2); ?></strong></p>
                    <!-- Formulario original para "Comprar" (redirige a procesar_compra.php con GET) -->
                    <form action="procesar_compra.php" method="get">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                        <button type="submit">Comprar</button>
                    </form>
                    <!-- Formulario original para "Agregar al carrito" (usa JS y POST a carrito.php) -->
                    <form class="form-agregar-carrito" data-producto-id="<?php echo $producto['id']; ?>" onsubmit="return agregarAlCarrito(event);">
                        <button type="submit">Agregar al carrito</button>
                    </form>
                </div>
            <?php endwhile; ?>
             <?php if ($resultado->num_rows === 0): ?>
                 <p>No hay productos disponibles en esta categoría.</p>
             <?php endif; ?>
        </section>
    </main>


    <script>
    // Script original para agregar al carrito usando fetch/POST a carrito.php
    function agregarAlCarrito(event) {
        event.preventDefault();
        const form = event.target;
        const productoId = form.getAttribute('data-producto-id');

        // Asegúrate de que la URL 'carrito.php' es correcta para tu proyecto
        fetch('carrito.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            // Enviamos producto_id y cantidad (asumimos 1 por defecto al agregar desde index)
            body: 'producto_id=' + encodeURIComponent(productoId) + '&cantidad=' + encodeURIComponent(1)
        })
        .then(response => response.json()) // Asumimos que carrito.php responde con JSON
        .then(data => {
            if (data.success) {
                alert('Producto agregado al carrito!');
                // Actualizar el contador en "Ver carrito"
                const carritoLink = document.querySelector('nav a[href="carrito.php"]');
                if (carritoLink) {
                    let texto = carritoLink.textContent;
                    // Extraer número actual y sumarle 1
                    let match = texto.match(/\((\d+)\)/);
                    if (match) {
                        let cantidad = parseInt(match[1]) + 1;
                        carritoLink.textContent = `Ver carrito (${cantidad})`;
                    } else {
                         // Si no encuentra el número, simplemente establece (1) si antes estaba vacío
                         carritoLink.textContent = `Ver carrito (1)`;
                    }
                }
            } else {
                // Si carrito.php devuelve success: false y un mensaje de error
                alert('Error al agregar producto: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
             console.error('Error:', error);
             alert('Error de conexión o del servidor al agregar producto.');
        });
        return false; // Prevenir el envío del formulario tradicional
    }

    // Este script parece no estar relacionado con el carrito o productos,
    // pero lo mantengo si era parte de tu código original.
      const container = document.querySelector(".container"); // Asegúrate de tener un elemento con clase .container
      const toggle = document.querySelector(".toggle"); // Asegúrate de tener un elemento con clase .toggle

    if (toggle && container) { // Verificar si los elementos existen antes de añadir el listener
        toggle.addEventListener("click", () => {
            container.classList.toggle("active");
        });
    }
    </script>

    <footer>
        <?php include 'includes/footer.php'; ?>
    </footer>
</body>
</html>
<?php
// Cerrar conexión si se abrió y no se cerró antes
if (isset($conexion) && $conexion->ping()) {
    $conexion->close();
}
?>
