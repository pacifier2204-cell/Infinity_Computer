<?php include __DIR__ . '/../auth_guard.php'; ?>
<?php
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    $res = $conn->query("
        SELECT s.*, c.name, c.phone 
        FROM services s 
        JOIN customers c ON s.customer_id = c.id 
        ORDER BY s.created_at DESC 
        LIMIT 50
    ");
    $services = [];
    if($res) {
        while($row = $res->fetch_assoc()) {
            $services[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $services]);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
