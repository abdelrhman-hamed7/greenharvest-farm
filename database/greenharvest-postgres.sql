-- GreenHarvest Farm PostgreSQL Database
-- Use this file for Render PostgreSQL and the Docker PostgreSQL setup.

DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS customer_accounts CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;

CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price NUMERIC(10, 2) NOT NULL CHECK (price >= 0),
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    image_path VARCHAR(255) DEFAULT NULL,
    is_featured SMALLINT NOT NULL DEFAULT 0 CHECK (is_featured IN (0, 1)),
    status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(is_featured);

CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customer_accounts (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    total_amount NUMERIC(10, 2) NOT NULL CHECK (total_amount >= 0),
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'completed', 'cancelled')),
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    payment_phone VARCHAR(30) NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (payment_status IN ('pending', 'paid', 'failed')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id)
        REFERENCES customers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);

CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    price NUMERIC(10, 2) NOT NULL CHECK (price >= 0),
    subtotal NUMERIC(10, 2) NOT NULL CHECK (subtotal >= 0),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);

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
