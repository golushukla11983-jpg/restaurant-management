CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    delivery_address TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (item_id) REFERENCES menu_items(id)
);

-- Insert Default Admin
INSERT INTO users (name, email, password, role) 
VALUES ('Admin', 'admin@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password

-- Insert Sample Menu Items
INSERT INTO menu_items (name, description, price, category, image) VALUES
('Margherita Pizza', 'Classic pizza with tomato sauce, mozzarella, and basil', 12.99, 'Pizza', '🍕'),
('Chicken Burger', 'Grilled chicken with lettuce, tomato, and special sauce', 9.99, 'Burgers', '🍔'),
('Caesar Salad', 'Fresh romaine lettuce with Caesar dressing and croutons', 7.99, 'Salads', '🥗'),
('Pasta Carbonara', 'Creamy pasta with bacon and parmesan', 13.99, 'Pasta', '🍝'),
('Grilled Salmon', 'Fresh salmon with vegetables and lemon butter', 18.99, 'Seafood', '🐟'),
('Chocolate Cake', 'Rich chocolate cake with chocolate frosting', 6.99, 'Desserts', '🍰');

ALTER TABLE menu_items 
MODIFY COLUMN image VARCHAR(255) DEFAULT NULL;