CREATE DATABASE IF NOT EXISTS cipher_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cipher_db;

CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(30),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shipments (
    shipment_id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    origin VARCHAR(150) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    shipment_type VARCHAR(50) DEFAULT 'Parcel',
    status ENUM('Processing','In Transit','Delivered','On Hold') DEFAULT 'Processing',
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    ship_date DATE NULL,
    estimated_delivery DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shipments_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS tracking_events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    location VARCHAR(150),
    description TEXT,
    event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tracking_shipment FOREIGN KEY (shipment_id) REFERENCES shipments(shipment_id) ON DELETE CASCADE
);
