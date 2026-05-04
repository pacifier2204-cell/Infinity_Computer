<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$enteredOtp = trim($input['otp'] ?? '');

// Check if OTP session exists
if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'No OTP request found. Please request a new OTP.', 'expired' => true]);
    exit;
}

// Check if OTP has expired (5 minutes)
if ((time() - $_SESSION['otp_timestamp']) > 300) {
    unset($_SESSION['otp_code'], $_SESSION['otp_email'], $_SESSION['otp_timestamp'], $_SESSION['otp_attempts']);
    echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.', 'expired' => true]);
    exit;
}

// Check max attempts
if ($_SESSION['otp_attempts'] >= 3) {
    echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Try again later.', 'blocked' => true]);
    exit;
}

// Validate input
if (empty($enteredOtp) || strlen($enteredOtp) !== 6) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 6-digit OTP']);
    exit;
}

// Verify OTP
if ($enteredOtp === $_SESSION['otp_code']) {
    // Success — create authenticated session
    $_SESSION['staff_logged_in'] = true;
    $_SESSION['staff_email'] = $_SESSION['otp_email'];
    $_SESSION['staff_login_time'] = time();
    $_SESSION['staff_last_activity'] = time();

    // Clean up OTP data
    unset($_SESSION['otp_code'], $_SESSION['otp_timestamp'], $_SESSION['otp_attempts'], $_SESSION['otp_last_sent']);

    echo json_encode(['status' => 'success', 'message' => 'Login successful!', 'redirect' => 'index.php']);
} else {
    $_SESSION['otp_attempts']++;
    $remaining = 3 - $_SESSION['otp_attempts'];

    if ($remaining <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Try again later.', 'blocked' => true]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Invalid OTP. {$remaining} attempt(s) remaining.", 'remaining' => $remaining]);
    }
}
?>
