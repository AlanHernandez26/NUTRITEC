<?php
session_start();
require_once 'db/conexion.php';


$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias->num_rows > 0) {
    while($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}


$categoria_seleccionada_id = null;
$filtro_sql = "";
$bind_params = [];
$bind_types = "";

if (isset($_GET['categoria_id']) && is_numeric($_GET['categoria_id'])) {
    $categoria_seleccionada_id = intval($_GET['categoria_id']);
    
    $filtro_sql = " WHERE p.categoria_id = ?";
    $bind_params[] = $categoria_seleccionada_id;
    $bind_types .= "i"; 
}


$cantidadProductos = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $cantidad) {
        $cantidadProductos += $cantidad;
    }
}


$sql = "SELECT p.*, c.nombre AS nombre_categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        " . $filtro_sql . " -- Insertamos el filtro aquí
        ORDER BY p.creado_en DESC";

$stmt = $conexion->prepare($sql);


if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

$stmt->execute();
$resultado = $stmt->get_result();



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NutriTec - Comida Saludable</title>
    <link rel="stylesheet" href="css/estilos.css">
     <style>
        
        .categorias-nav {
            margin-bottom: 20px;
            padding: 10px;
            background-color: 
            border-radius: 5px;
        }
        .categorias-nav a {
            margin-right: 15px;
            text-decoration: none;
            color: 
            font-weight: bold;
        }
        .categorias-nav a:hover {
            color: 
        }
         .categorias-nav a.active {
            color: 
            text-decoration: underline;
        }
         
         
         .contenedor-productos {
             display: grid;
             grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
             gap: 20px;
         }
         .producto {
             border: 1px solid 
             padding: 15px;
             text-align: center;
             border-radius: 8px;
             background-color: white;
         }
         .producto img {
             max-width: 100%;
             height: 150px; 
             object-fit: cover;
             margin-bottom: 10px;
             border-radius: 4px;
         }
          .producto h3 {
             margin-top: 0;
             font-size: 1.2em;
             height: 3em; 
             overflow: hidden;
             text-overflow: ellipsis;
         }
          .producto p {
              font-size: 0.9em;
              color: 
              height: 4em; 
              overflow: hidden;
              text-overflow: ellipsis;
          }
         .producto strong {
             font-size: 1.1em;
             color: 
             margin: 10px 0;
             display: block;
         }
          .producto form {
              display: inline-block;
              margin: 5px;
          }
          .producto button {
             background-color: 
             color: white;
             padding: 8px 15px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
          }
          .producto button:hover {
             background-color: 
          }
     </style>
</head>
<body>
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

    <main class="container"> 
        <h2>Nuestros Productos</h2>

        
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
                    
                    <form action="procesar_compra.php" method="get">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                        <button type="submit">Comprar</button>
                    </form>
                    
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
    
    function agregarAlCarrito(event) {
        event.preventDefault();
        const form = event.target;
        const productoId = form.getAttribute('data-producto-id');

        
        fetch('carrito.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            
            body: 'producto_id=' + encodeURIComponent(productoId) + '&cantidad=' + encodeURIComponent(1)
        })
        .then(response => response.json()) 
        .then(data => {
            if (data.success) {
                alert('Producto agregado al carrito!');
                
                const carritoLink = document.querySelector('nav a[href="carrito.php"]');
                if (carritoLink) {
                    let texto = carritoLink.textContent;
                    
                    let match = texto.match(/\((\d+)\)/);
                    if (match) {
                        let cantidad = parseInt(match[1]) + 1;
                        carritoLink.textContent = `Ver carrito (${cantidad})`;
                    } else {
                         
                         carritoLink.textContent = `Ver carrito (1)`;
                    }
                }
            } else {
                
                alert('Error al agregar producto: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
             console.error('Error:', error);
             alert('Error de conexión o del servidor al agregar producto.');
        });
        return false; 
    }

    
    
      const container = document.querySelector(".container"); 
      const toggle = document.querySelector(".toggle"); 

    if (toggle && container) { 
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

if (isset($conexion) && $conexion->ping()) {
    $conexion->close();
}
?>
