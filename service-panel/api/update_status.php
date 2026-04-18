<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($data)) {
        $data = $_POST;
    }
    
    $service_pk = $data['id'] ?? '';
    $new_status = $data['status'] ?? '';
    $remarks = $data['remarks'] ?? '';

    if(empty($service_pk) || empty($new_status)) {
        echo json_encode(['status' => 'error', 'message' => 'ID and status are required']);
        exit;
    }

    try {
        $conn->begin_transaction();

        if(in_array($new_status, ['Completed', 'Ready for Pickup', 'Delivered'])) {
            $stmt = $conn->prepare("UPDATE services SET status = ?, date_completed = ? WHERE id = ?");
            $date = date('Y-m-d');
            $stmt->bind_param("ssi", $new_status, $date, $service_pk);
        } else {
            $stmt = $conn->prepare("UPDATE services SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $service_pk);
        }
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO service_status_logs (service_id, status, remarks) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $service_pk, $new_status, $remarks);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
    } catch(Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
