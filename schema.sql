CREATE DATABASE IF NOT EXISTS inventaris_id;

USE inventaris_id;

DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;


CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);


CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL
);


CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL,

    FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


INSERT INTO categories (name) VALUES
('Laptop'),
('Smartphone'),
('Aksesoris'),
('Perangkat Jaringan'),
('Elektronik');


INSERT INTO suppliers (name, phone) VALUES
('PT Teknologi Nusantara', '081265789350'),
('CV Digitalies', '089510283746'),
('PT Sumber Komputer', '082309874465'),
('CV Mitra Elektronik', '083128304958'),
('PT Solusi Digital', '087612348900');


INSERT INTO products
(name, category_id, supplier_id, price, stock)
VALUES
('Axioo Hype 5 X6', 1, 1, 8249000, 5),
('Xiaomi Redmi Note 17 5G', 2, 3, 4899000, 10),
('Bluetooth Mouse Logitech M196', 3, 5, 210000, 25),
('Mesh WiFi TP-Link', 4, 4, 419000, 20),
('Monitor LG 24 Inch', 5, 2, 2600000, 15);