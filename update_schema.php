<?php
// update_schema.php
$conn = new mysqli('localhost', 'root', '', 'infinity_students');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "ALTER TABLE students ADD COLUMN verification ENUM('Pending', 'Successful', 'Cancelled') DEFAULT 'Pending' AFTER id_proof_path";

if ($conn->query($sql) === TRUE) {
    echo "Column 'verification' added successfully.";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>
