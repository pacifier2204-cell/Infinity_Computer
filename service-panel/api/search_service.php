<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if(empty($query)) {
    echo json_encode(['status' => 'error', 'message' => 'Query is required']);
    exit;
}

try {
    $results = [];

    // 1. Search Active/Engineering Services
    $stmt = $conn->prepare("
        SELECT s.*, c.name, c.phone, 'engineering' as source_type
        FROM services s 
        JOIN customers c ON s.customer_id = c.id 
        WHERE s.service_id = ? OR c.phone = ? OR c.name = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->bind_param("sss", $query, $query, $query);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while($row = $res->fetch_assoc()) {
        // Fetch logs for engineering services
        $log_stmt = $conn->prepare("SELECT * FROM service_status_logs WHERE service_id = ? ORDER BY updated_at DESC");
        $log_stmt->bind_param("i", $row['id']);
        $log_stmt->execute();
        $log_res = $log_stmt->get_result();
        $row['logs'] = [];
        while($log_row = $log_res->fetch_assoc()) {
            $row['logs'][] = $log_row;
        }
        $results[] = $row;
    }

    // 2. Search User Service Requests (Web Requests)
    $stmt2 = $conn->prepare("
        SELECT *, 'web_request' as source_type 
        FROM user_service_requests 
        WHERE service_id = ? OR phone = ? OR name = ? 
        ORDER BY created_at DESC
    ");
    $stmt2->bind_param("sss", $query, $query, $query);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while($row = $res2->fetch_assoc()) {
        $results[] = $row;
    }

    // 3. Search Home Service Requests
    $stmt3 = $conn->prepare("
        SELECT *, 'home' as source_type 
        FROM home_service_requests 
        WHERE service_id = ? OR phone = ? OR name = ? 
        ORDER BY created_at DESC
    ");
    $stmt3->bind_param("sss", $query, $query, $query);
    $stmt3->execute();
    $res3 = $stmt3->get_result();
    while($row = $res3->fetch_assoc()) {
        $results[] = $row;
    }

    if(count($results) > 0) {
        echo json_encode(['status' => 'success', 'data' => $results]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No matching records found']);
    }
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
