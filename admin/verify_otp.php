<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$enteredOtp = isset($_POST['otp']) ? sanitizeInput($_POST['otp']) : '';

// 1. Check if OTP exists in session
if (!isset($_SESSION['admin_otp']) || !isset($_SESSION['admin_otp_expiry'])) {
    echo json_encode(['success' => false, 'message' => 'No OTP request found. Please generate a new one.']);
    exit;
}

// 2. Check expiration
if (time() > $_SESSION['admin_otp_expiry']) {
    unset($_SESSION['admin_otp']);
    unset($_SESSION['admin_otp_expiry']);
    echo json_encode(['success' => false, 'message' => 'OTP expired. Please request a new OTP.']);
    exit;
}

// 3. Verify OTP
if ($enteredOtp === $_SESSION['admin_otp']) {
    // Success!
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = $_SESSION['admin_otp_email'];
    
    // Clear OTP data
    unset($_SESSION['admin_otp']);
    unset($_SESSION['admin_otp_expiry']);
    unset($_SESSION['resend_count']);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
}
exit;
?>
