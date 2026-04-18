<?php
// Requesting the main database connection from your root config folder
require_once __DIR__ . '/../../config/db.php';

// Make sure connection exists
if (!isset($conn) || $conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}
?>
