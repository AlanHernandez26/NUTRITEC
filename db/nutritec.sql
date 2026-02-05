-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS nutritec;
USE nutritec;

-- Tabla de usuarios (admin, gerente, cajero)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    rol ENUM('admin', 'gerente', 'cajero') NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos (platillos saludables)
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    costo DECIMAL(8, 2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    imagen_url TEXT,
    destacado BOOLEAN DEFAULT FALSE,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de pedidos (cada compra realizada)
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(8, 2) NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta') NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Detalles del pedido (productos dentro de un pedido)
CREATE TABLE detalles_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(8, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Quejas y sugerencias
CREATE TABLE quejas_sugerencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo ENUM('queja', 'sugerencia') NOT NULL,
    mensaje TEXT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
-- Tabla de roles (para definir permisos)
ALTER TABLE pedidos
ADD COLUMN entregado_por_cajero_id INT NULL;


-- Crear la tabla categorias
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Añadir la columna categoria_id a la tabla productos
ALTER TABLE productos
ADD COLUMN categoria_id INT NULL AFTER categoria; -- Añadimos después de la columna 'categoria' existente

-- Añadir una clave foránea para relacionar productos con categorias
-- Esto asume que ya tienes una columna 'categoria' VARCHAR en productos.
-- Si quieres eliminar la columna 'categoria' VARCHAR después de migrar los datos, puedes hacerlo.
ALTER TABLE productos
ADD CONSTRAINT fk_producto_categoria
FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL;

-- Insertar las categorías iniciales
INSERT INTO categorias (nombre) VALUES
('Guisados completos'),
('Caldos'),
('Snacks'),
('Postres'),
('Desayunos'),
('Comidas completas'),
('Guarniciones'),
('Bebidas'),
('Extras'); -- 'Extras' para comentarios detallados, aunque esto podría manejarse mejor en detalles_pedido

-- Puedes añadir más categorías si lo consideras necesario, por ejemplo:
-- ('Ensaladas'),
-- ('Sopas Frias');

