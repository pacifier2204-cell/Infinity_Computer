<?php
require_once 'config/db.php';

$sql = "ALTER TABLE second_hand_laptop_requests ADD COLUMN gadget_type VARCHAR(50) AFTER address";
if ($conn->query($sql)) {
    echo "Column gadget_type added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
$conn->close();
?>
