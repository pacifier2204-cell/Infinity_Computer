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

if (!$id || !in_array($status, ['Successful', 'Cancelled'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

// Fetch student details before update for email
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student record not found.']);
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE students SET verification = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Send Email Notification and capture result
    $emailResult = sendVerificationEmail($student['email'], $student['student_id'], $student['name'], $status);
    
    $actionText = ($status === 'Successful' ? 'verified' : 'rejected');
    $response = [
        'success' => true, 
        'message' => "Student enrollment {$actionText} successfully.",
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
function sendVerificationEmail($to, $sid, $studentName, $status) {
    // 1. Validate email format
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $reason = "Invalid or empty email address: '{$to}' for student {$sid}";
        error_log("Email Notification Failed - " . $reason);
        return ['success' => false, 'reason' => "Email could not be sent — invalid email address ({$to})."];
    }

    // 2. Check if domain has MX records (basic deliverability check)
    $domain = substr(strrchr($to, "@"), 1);
    if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
        $reason = "Email domain '{$domain}' has no MX/A records for student {$sid}";
        error_log("Email Notification Failed - " . $reason);
        return ['success' => false, 'reason' => "Email could not be sent — the domain '{$domain}' does not appear to accept emails."];
    }

    // 3. Build email content
    if ($status === 'Successful') {
        $subject = "Enrollment Approved - Infinity Computer";
        $message = "Dear {$studentName},\n\nYour enrollment has been successfully verified. You are now officially enrolled.\n\nStudent ID: $sid\n\nFor any further assistance, please contact us.\n\nBest Regards,\nInfinity Computer Team";
    } else {
        $subject = "Enrollment Application Update - Infinity Computer";
        $message = "Dear {$studentName},\n\nWe regret to inform you that your enrollment application has been rejected after verification.\n\nFor further assistance, please contact support.\n\nBest Regards,\nInfinity Computer Team";
    }

    $headers = "From: noreply@infinitycomputer.com\r\n";
    $headers .= "Reply-To: noreply@infinitycomputer.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // 4. Attempt to send
    $mailSent = @mail($to, $subject, $message, $headers);

    if (!$mailSent) {
        $reason = "PHP mail() failed for student {$sid} (email: {$to})";
        error_log("Email Notification Failed - " . $reason);
        return ['success' => false, 'reason' => "Email could not be delivered to {$to}. The mail server may not be configured or the address may be unreachable."];
    }

    return ['success' => true];
}
