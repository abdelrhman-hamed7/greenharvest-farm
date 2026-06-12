-- GreenHarvest Farm Database
-- Course project: EWA408510 - E-Commerce and Web Application
-- Import this file in phpMyAdmin, MySQL Workbench, or the MySQL command line.

CREATE DATABASE IF NOT EXISTS greenharvest_farm
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE greenharvest_farm;

-- Drop child tables first so the script can be re-imported during development.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS customer_accounts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_category (category_id),
    INDEX idx_products_status (status),
    INDEX idx_products_featured (is_featured),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_products_price CHECK (price >= 0),
    CONSTRAINT chk_products_stock CHECK (stock >= 0)
) ENGINE=InnoDB;

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customer_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    payment_phone VARCHAR(30) NULL,
    payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orders_customer (customer_id),
    INDEX idx_orders_status (status),
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id)
        REFERENCES customers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_orders_total CHECK (total_amount >= 0)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_order_items_price CHECK (price >= 0),
    CONSTRAINT chk_order_items_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB;

INSERT INTO categories (name, description) VALUES
('Vegetables', 'Fresh organic vegetables harvested from GreenHarvest Farm.'),
('Fruits', 'Naturally grown seasonal fruits full of flavor and nutrients.'),
('Dairy', 'Fresh dairy products from healthy farm animals.'),
('Coffee & Tea', 'Organic coffee and herbal tea grown and prepared with care.'),
('Farm Products', 'Daily farm essentials including honey, eggs, grains, and baskets.');

INSERT INTO products
(category_id, name, description, price, stock, image_path, is_featured, status)
VALUES
(1, 'Fresh Tomatoes', 'Juicy organic tomatoes harvested fresh from our greenhouse. Perfect for salads, sauces, and cooking.', 3500.00, 80, NULL, 1, 'active'),
(1, 'Carrots', 'Crunchy farm carrots grown in rich organic soil. Great for soups, juices, and healthy snacks.', 2200.00, 100, NULL, 1, 'active'),
(1, 'Cabbage', 'Fresh green cabbage with crisp leaves, ideal for salads, stews, and traditional meals.', 2800.00, 60, NULL, 0, 'active'),
(2, 'Avocado', 'Creamy organic avocados picked at the right time for great taste and texture.', 4000.00, 50, NULL, 1, 'active'),
(2, 'Bananas', 'Naturally sweet bananas from our farm, perfect for breakfast, smoothies, and desserts.', 3000.00, 120, NULL, 1, 'active'),
(2, 'Pineapple', 'Sweet tropical pineapple with bright flavor, freshly harvested and ready to enjoy.', 5500.00, 35, NULL, 0, 'active'),
(3, 'Fresh Milk', 'Pure fresh milk from our dairy section, carefully handled for quality and freshness.', 2500.00, 40, NULL, 1, 'active'),
(5, 'Farm Eggs', 'Nutritious farm eggs collected daily from healthy free-range hens.', 4500.00, 90, NULL, 1, 'active'),
(4, 'Organic Coffee', 'Aromatic organic coffee with a smooth farm-grown taste. Great for daily brewing.', 8000.00, 45, NULL, 1, 'active'),
(4, 'Herbal Tea', 'Refreshing herbal tea blend made from carefully selected farm herbs.', 6000.00, 55, NULL, 0, 'active'),
(5, 'Pure Honey', 'Natural honey with rich sweetness, collected and packed with care.', 7500.00, 30, NULL, 1, 'active'),
(5, 'Beans', 'Clean dry beans from the farm, rich in protein and ideal for family meals.', 4200.00, 100, NULL, 0, 'active'),
(5, 'Maize Flour', 'Freshly milled maize flour for porridge, baking, and traditional recipes.', 3800.00, 75, NULL, 0, 'active'),
(5, 'Gift Basket', 'A beautiful farm gift basket with selected seasonal products for friends and family.', 18000.00, 20, NULL, 1, 'active');
