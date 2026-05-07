<?php include __DIR__ . '/../auth_guard.php'; ?>
<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if(empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Service ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT s.*, c.name, c.phone 
        FROM services s 
        JOIN customers c ON s.customer_id = c.id 
        WHERE s.service_id = ?
    ");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $svc = $res->fetch_assoc();

    if($svc) {
        $log_stmt = $conn->prepare("SELECT * FROM service_status_logs WHERE service_id = ? ORDER BY updated_at DESC");
        $log_stmt->bind_param("i", $svc['id']);
        $log_stmt->execute();
        $log_res = $log_stmt->get_result();
        $svc['logs'] = [];
        while($row = $log_res->fetch_assoc()) {
            $svc['logs'][] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $svc]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Service not found']);
    }
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
