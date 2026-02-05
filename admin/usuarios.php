<?php
session_start();

// Solo admins pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Conexión
$conn = new mysqli("localhost", "root", "", "nutritec");
if ($conn->connect_error) {
    die("Error en conexión: " . $conn->connect_error);
}

// Crear nuevo usuario
$mensaje = "";
if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validar si el correo ya existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = "❌ El correo ya está registrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, creado_en) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $nombre, $correo, $password, $rol);
        $stmt->execute();
        $stmt->close();
        $mensaje = "✅ Usuario creado correctamente.";
    }
    $check->close();
}

// Editar usuario
if (isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $rol = $_POST['rol'];

    $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->bind_param("si", $rol, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: usuarios.php");
    exit;
}

// Eliminar usuario
if (isset($_POST['eliminar'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: usuarios.php");
    exit;
}

// Consultas
$usuarios_con_rol = $conn->query("SELECT * FROM usuarios WHERE rol IS NOT NULL AND rol != ''");
$clientes = $conn->query("SELECT * FROM usuarios WHERE rol IS NULL OR rol = ''");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(to right, #00704A, #1E3932);
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        nav a {
            color: #fff;
            margin: 0 15px;
            text-decoration: none;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-editar, .btn-eliminar {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        .btn-editar {
            background-color: #4CAF50;
            color: white;
        }
        .btn-eliminar {
            background-color: #f44336;
            color: white;
        }
        form label {
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 7px;
            margin-bottom: 10px;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            background-color: #e0ffe0;
            border: 1px solid #8bc34a;
            border-radius: 5px;
            color: #33691e;
        }
        .error {
            background-color: #ffdddd;
            border: 1px solid #e53935;
            color: #b71c1c;
        }
    </style>
</head>
<body>
<header>
    <h1>Panel de Administración - Usuarios</h1>
    <nav>
        <a href="dashboard.php">Inicio</a>
        <a href="../logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="container">
    <section style="margin-bottom: 40px;">
        <h2>Crear nuevo usuario</h2>
        <?php if ($mensaje): ?>
            <div class="mensaje <?= strpos($mensaje, '❌') !== false ? 'error' : '' ?>">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>
        <form method="POST" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Correo:</label>
            <input type="email" name="correo" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <label>Rol:</label>
            <select name="rol" required>
                <option value="">Seleccionar rol</option>
                <option value="admin">Administrador</option>
                <option value="gerente">Gerente</option>
                <option value="cajero">Cajero</option>
            </select>

            <button type="submit" name="crear" class="btn-editar">Agregar Usuario</button>
        </form>
    </section>

    <section>
        <h2>Usuarios con rol (administradores, gerentes, cajeros)</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th><th>Correo</th><th>Rol</th><th>Registrado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $usuarios_con_rol->fetch_assoc()): ?>
                    <tr>
                        <form method="POST">
                            <td><?= htmlspecialchars($row['nombre']); ?></td>
                            <td><?= htmlspecialchars($row['correo']); ?></td>
                            <td>
                                <select name="rol" required>
                                    <option value="admin" <?= $row['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="gerente" <?= $row['rol'] === 'gerente' ? 'selected' : ''; ?>>Gerente</option>
                                    <option value="cajero" <?= $row['rol'] === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                                </select>
                            </td>
                            <td><?= $row['creado_en']; ?></td>
                            <td>
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <button type="submit" name="editar" class="btn-editar">Guardar</button>
                                <button type="submit" name="eliminar" class="btn-eliminar" onclick="return confirm('¿Seguro que quieres eliminar este usuario?')">Eliminar</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Clientes (sin rol)</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th><th>Correo</th><th>Registrado</th><th>Asignar rol</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $clientes->fetch_assoc()): ?>
                    <tr>
                        <form method="POST">
                            <td><?= htmlspecialchars($row['nombre']); ?></td>
                            <td><?= htmlspecialchars($row['correo']); ?></td>
                            <td><?= $row['creado_en']; ?></td>
                            <td>
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <select name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="admin">Administrador</option>
                                    <option value="gerente">Gerente</option>
                                    <option value="cajero">Cajero</option>
                                </select>
                                <button type="submit" name="editar" class="btn-editar">Asignar rol</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>

<footer>
    <p style="text-align: center; padding: 20px;">&copy; <?= date("Y"); ?> NutriTec - Gestión de usuarios</p>
</footer>
</body>
</html>
