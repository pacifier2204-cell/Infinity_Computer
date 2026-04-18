<?php
require_once '../config/db.php';

try {
    $conn->query("CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id VARCHAR(50) NOT NULL UNIQUE,
        customer_id INT NOT NULL,
        service_type VARCHAR(100) NOT NULL,
        device_name VARCHAR(255) NOT NULL,
        problem TEXT NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        date_received DATE NOT NULL,
        date_completed DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS service_status_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        remarks TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id)
    )");

    echo json_encode(['status' => 'success', 'message' => 'Database tables have been successfully created inside your infinity_students database!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
