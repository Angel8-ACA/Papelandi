-- ============================================================
-- PAPELERÍA EL RINCÓN DEL SABER v2
-- Con login de usuarios
-- ============================================================

CREATE DATABASE IF NOT EXISTS papeleria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE papeleria_db;

-- ── USUARIOS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin','cliente') DEFAULT 'cliente',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── CATEGORÍAS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    icono       VARCHAR(10)  DEFAULT '📦',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── PRODUCTOS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio      DECIMAL(10,2) NOT NULL,
    stock       INT DEFAULT 0,
    categoria_id INT,
    destacado   TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- ── VENTAS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ventas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT,
    total       DECIMAL(10,2) NOT NULL,
    fecha       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ── DETALLE VENTAS ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS detalle_ventas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    venta_id        INT,
    producto_id     INT,
    cantidad        INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id)    REFERENCES ventas(id)   ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

-- ── DATOS DE EJEMPLO ──────────────────────────────────────

-- Usuarios (password = password_hash de 'admin123' y 'cliente123')
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador',  'admin@rincon.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('María García',   'maria@ejemplo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Carlos López',   'carlos@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');
-- NOTA: La contraseña para TODOS los usuarios es: password

INSERT INTO categorias (nombre, icono) VALUES
('Escritura',  '✏️'),
('Papel',      '📄'),
('Arte',       '🎨'),
('Oficina',    '📎'),
('Tecnología', '💾'),
('Mochilas',   '🎒');

INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, destacado) VALUES
('Pluma BIC Cristal Azul',    'Pluma de tinta azul de larga duración, punta media.',         5.50,  200, 1, 1),
('Lápiz HB Staedtler',        'Lápiz de grafito HB, ideal para escribir y dibujar.',          3.00,  150, 1, 0),
('Marcador Sharpie Negro',    'Marcador permanente punta fina, color negro.',                 18.00,  80, 1, 1),
('Set 12 Colores Prismacolor','Lápices de colores profesionales Prismacolor.',               120.00,  30, 1, 1),
('Cuaderno Scribe 100 hojas', 'Cuaderno profesional 100 hojas raya, tamaño carta.',           35.00, 100, 2, 0),
('Libreta Moleskine A5',      'Libreta clásica Moleskine tapa dura, páginas lisas.',         180.00,  20, 2, 1),
('Hojas Bond 500 pzas',       'Papel bond blanco 75g, paquete de 500 hojas.',                 85.00,  50, 2, 0),
('Hojas Opalina Colores',     'Pack 100 hojas opalina de colores para manualidades.',          45.00,  60, 2, 0),
('Acuarelas Pelikan 24',      'Set de acuarelas Pelikan 24 colores con pincel incluido.',     95.00,  25, 3, 1),
('Pegamento Resistol 850ml',  'Resistol blanco escolar, envase grande 850ml.',                42.00,  70, 3, 0),
('Tijeras Maped 21cm',        'Tijeras de acero inoxidable con mango ergonómico.',            55.00,  45, 4, 0),
('Clips Mariposa 100pz',      'Clips mariposa metálicos, caja de 100 piezas.',                28.00,  90, 4, 0),
('Cinta Canela 24mm',         'Cinta adhesiva transparente 24mm × 50m.',                      15.00, 120, 4, 0),
('Grapadora Rapid',           'Grapadora metálica capacidad 25 hojas, muy resistente.',      110.00,  35, 4, 1),
('USB Kingston 32GB',         'Memoria USB 3.0 Kingston DataTraveler 32GB.',                 145.00,  40, 5, 1),
('Pilas Duracell AA ×4',      'Pilas alcalinas Duracell AA, paquete de 4 unidades.',          48.00,  80, 5, 0),
('Mochila Nike 30L',          'Mochila escolar Nike con compartimento acolchado para laptop.',580.00, 15, 6, 1),
('Mochila Infantil Dinos',    'Mochila infantil con estampado de dinosaurios, 15L.',          220.00,  25, 6, 0);
