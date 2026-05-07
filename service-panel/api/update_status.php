<?php include __DIR__ . '/../auth_guard.php'; ?>
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

        // Fetch service details for email
        $e_stmt = $conn->prepare("SELECT s.service_id, s.device_name, c.name, c.email FROM services s JOIN customers c ON s.customer_id = c.id WHERE s.id = ?");
        $e_stmt->bind_param("i", $service_pk);
        $e_stmt->execute();
        $srv_data = $e_stmt->get_result()->fetch_assoc();
        
        if ($srv_data) {
            $email = $srv_data['email'];
            $srv_id = $srv_data['service_id'];
            
            // If email is not in customers table, try user_service_requests
            if (empty($email)) {
                $usr_stmt = $conn->prepare("SELECT email FROM user_service_requests WHERE service_id = ?");
                $usr_stmt->bind_param("s", $srv_id);
                $usr_stmt->execute();
                $usr_res = $usr_stmt->get_result()->fetch_assoc();
                if ($usr_res && !empty($usr_res['email'])) {
                    $email = $usr_res['email'];
                }
            }

            if (!empty($email)) {
                require_once 'email_helper.php';
                sendServiceStatusUpdateEmail($email, $srv_data['name'], $srv_id, $new_status, $srv_data['device_name']);
            }
        }

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
