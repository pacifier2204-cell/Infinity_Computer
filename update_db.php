<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'infinity_students');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "ALTER TABLE students 
        ADD COLUMN IF NOT EXISTS photo_path VARCHAR(255) AFTER time_slot,
        ADD COLUMN IF NOT EXISTS id_proof_path VARCHAR(255) AFTER photo_path,
        ADD COLUMN IF NOT EXISTS marksheet_path VARCHAR(255) AFTER id_proof_path";

if ($conn->query($sql) === TRUE) {
    echo "Columns checked/added successfully";
} else {
    // IF NOT EXISTS might not be supported in older MySQL, handle manually if needed
    echo "Error: " . $conn->error;
}
$conn->close();
?>
