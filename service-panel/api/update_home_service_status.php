<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'ID and Status are required']);
        exit;
    }

    try {
        // Fetch existing record to get details for email
        $stmt = $conn->prepare("SELECT * FROM home_service_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();

        if (!$record) {
            throw new Exception("Record not found");
        }

        $stmt = $conn->prepare("UPDATE home_service_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            if ($status != $record['status']) {
                require_once 'email_helper.php';
                sendHomeServiceStatusEmail($record['email'], $record['name'], $record['service_id'], $status);
            }
            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
        } else {
            throw new Exception("Update failed");
        }
    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
