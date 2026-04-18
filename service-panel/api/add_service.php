<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $device_name = $_POST['device_name'] ?? '';
    $problem = $_POST['problem'] ?? '';
    
    if (empty($name) || empty($phone) || empty($service_type) || empty($device_name) || empty($problem)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $res = $stmt->get_result();
        $customer = $res->fetch_assoc();

        if ($customer) {
            $customer_id = $customer['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $phone);
            $stmt->execute();
            $customer_id = $conn->insert_id;
        }

        $year = date("Y");
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM services WHERE YEAR(created_at) = ?");
        $stmt->bind_param("s", $year);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        
        $next_num = $row['count'] + 1;
        $service_id = "INF-" . $year . "-" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
        
        $stmt = $conn->prepare("SELECT id FROM services WHERE service_id = ?");
        while(true) {
            $stmt->bind_param("s", $service_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if(!$res->fetch_assoc()) break;
            $next_num++;
            $service_id = "INF-" . $year . "-" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
        }

        $image_path = null;
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/images/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $filename = time() . '_' . basename($_FILES["image"]["name"]);
            $filename = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $filename);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/images/" . $filename;
            }
        }

        $date_received = date('Y-m-d');
        
        $stmt = $conn->prepare("INSERT INTO services (service_id, customer_id, service_type, device_name, problem, image_path, status, date_received) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
        $stmt->bind_param("sisssss", $service_id, $customer_id, $service_type, $device_name, $problem, $image_path, $date_received);
        $stmt->execute();
        $service_pk = $conn->insert_id;

        $stmt = $conn->prepare("INSERT INTO service_status_logs (service_id, status, remarks) VALUES (?, 'Pending', 'Service request created')");
        $stmt->bind_param("i", $service_pk);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['status' => 'success', 'service_id' => $service_id, 'message' => 'Service added successfully']);

    } catch(Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
