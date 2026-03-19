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
    // Send Email Notification
    sendVerificationEmail($student['email'], $student['student_id'], $status);
    
    echo json_encode(['success' => true, 'message' => "Student enrollment " . ($status === 'Successful' ? 'verified' : 'rejected') . " successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
$stmt->close();

/**
 * Send Email Notification using mail()
 */
function sendVerificationEmail($to, $sid, $status) {
    if ($status === 'Successful') {
        $subject = "Enrollment Approved - Infinity Computer";
        $message = "Dear Student,\n\nYour enrollment has been successfully verified. You are now officially enrolled.\n\nStudent ID: $sid\n\nFor any further assistance, please contact us.\n\nBest Regards,\nInfinity Computer Team";
    } else {
        $subject = "Enrollment Application Update - Infinity Computer";
        $message = "Dear Student,\n\nWe regret to inform you that your enrollment application has been rejected after verification.\n\nFor further assistance, please contact support.\n\nBest Regards,\nInfinity Computer Team";
    }

    $headers = "From: noreply@infinitycomputer.com\r\n";
    $headers .= "Reply-To: noreply@infinitycomputer.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Use @ to suppress errors if mail server is not configured on local env
    $mailSent = mail($to, $subject, $message, $headers);

    if (!$mailSent) {
        error_log("Mail failed for student ID: $sid");
    }
}
