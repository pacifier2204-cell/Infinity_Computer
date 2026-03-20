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
    // Send Email Notification and capture result
    $emailResult = sendLaptopStatusEmail($laptop['email'], $laptop['owner_name'], $laptop['laptop_company'] . ' ' . $laptop['laptop_model'], $status);
    
    $actionText = ($status === 'Verified' ? 'verified' : 'rejected');
    $response = [
        'success' => true, 
        'message' => "Laptop request {$actionText} successfully.",
        'email_status' => $emailResult['success'] ? 'sent' : 'failed',
    ];
    
    // If email failed, add warning so admin is notified
    if (!$emailResult['success']) {
        $response['email_warning'] = $emailResult['reason'];
    }
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
$stmt->close();

/**
 * Send Email Notification using mail()
 * Returns array with 'success' (bool) and 'reason' (string) on failure
 */
function sendLaptopStatusEmail($to, $owner, $laptopName, $status) {
    // 1. Validate email format
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $reason = "Invalid or empty email address: '{$to}' for owner {$owner}";
        error_log("Email Notification Failed - " . $reason);
        return ['success' => false, 'reason' => "Email could not be sent — invalid email address ({$to})."];
    }

    // 2. Check if domain has MX records (basic deliverability check)
    $domain = substr(strrchr($to, "@"), 1);
    if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
        $reason = "Email domain '{$domain}' has no MX/A records for owner {$owner}";
        error_log("Email Notification Failed - " . $reason);
        return ['success' => false, 'reason' => "Email could not be sent — the domain '{$domain}' does not appear to accept emails."];
    }

    // 3. Build email content
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

    // 4. Attempt to send
    $mailSent = @mail($to, $subject, $message, $headers);

    if (!$mailSent) {
        $reason = "PHP mail() failed for owner {$owner} (email: {$to})";
        error_log("Email Notification Failed - " . $reason);
        return ['success' => false, 'reason' => "Email could not be delivered to {$to}. The mail server may not be configured or the address may be unreachable."];
    }

    return ['success' => true];
}
?>
