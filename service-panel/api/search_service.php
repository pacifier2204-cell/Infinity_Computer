<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if(empty($query)) {
    echo json_encode(['status' => 'error', 'message' => 'Query is required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT s.*, c.name, c.phone 
        FROM services s 
        JOIN customers c ON s.customer_id = c.id 
        WHERE s.service_id = ? OR c.phone = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $services = [];
    while($row = $res->fetch_assoc()) {
        $services[] = $row;
    }

    if(count($services) > 0) {
        foreach($services as &$svc) {
            $log_stmt = $conn->prepare("SELECT * FROM service_status_logs WHERE service_id = ? ORDER BY updated_at DESC");
            $log_stmt->bind_param("i", $svc['id']);
            $log_stmt->execute();
            $log_res = $log_stmt->get_result();
            $svc['logs'] = [];
            while($log_row = $log_res->fetch_assoc()) {
                $svc['logs'][] = $log_row;
            }
        }
        echo json_encode(['status' => 'success', 'data' => $services]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No services found']);
    }
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
