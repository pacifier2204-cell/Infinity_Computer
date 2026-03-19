<?php
require_once 'config.php';
checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id || !in_array($status, ['Verified', 'Rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

// Fetch laptop details for email
$stmt = $conn->prepare("SELECT * FROM second_hand_laptop_requests WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$laptop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$laptop) {
    echo json_encode(['success' => false, 'message' => 'Request record not found.']);
    exit;
}

// Update status
if ($status === 'Verified') {
    $stmt = $conn->prepare("UPDATE second_hand_laptop_requests SET status = ?, verified_at = CURRENT_TIMESTAMP WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE second_hand_laptop_requests SET status = ? WHERE id = ?");
}
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Send Email Notification
    sendLaptopStatusEmail($laptop['email'], $laptop['owner_name'], $laptop['laptop_company'] . ' ' . $laptop['laptop_model'], $status);
    
    echo json_encode(['success' => true, 'message' => "Laptop request " . ($status === 'Verified' ? 'verified' : 'rejected') . " successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
$stmt->close();

/**
 * Send Email Notification using mail()
 */
function sendLaptopStatusEmail($to, $owner, $laptopName, $status) {
    if ($status === 'Verified') {
        $subject = "Laptop Request Verified - Infinity Computer";
        $message = "Dear $owner,\n\nYour second-hand laptop request has been successfully verified. \n\nLaptop: $laptopName\n\nInstructions: Please visit Infinity Computer Shop in person with the same laptop and your address proof document for a physical inspection. Final price will be decided after physical inspection.\n\nShop Visit Details: Please visit our shop between 10 AM to 6 PM on any working day.\n\nBest Regards,\nInfinity Computer Team";
    } else {
        $subject = "Laptop Request Update - Infinity Computer";
        $message = "Dear $owner,\n\nWe regret to inform you that your second-hand laptop request (Laptop: $laptopName) has been rejected.\n\nBased on the provided details, we cannot offer a buyback at this time. For further assistance, please contact support.\n\nBest Regards,\nInfinity Computer Team";
    }

    $headers = "From: noreply@infinitycomputer.com\r\n";
    $headers .= "Reply-To: noreply@infinitycomputer.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    @mail($to, $subject, $message, $headers);
}
?>
