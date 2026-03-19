<?php
$conn = new mysqli('localhost', 'root', '', 'infinity_students');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$result = $conn->query("DESCRIBE students");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' ' . $row['Type'] . PHP_EOL;
}
?>
