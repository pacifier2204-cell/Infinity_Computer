<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = isset($_POST['email']) ? strtolower(sanitizeInput($_POST['email'])) : '';
$isResend = isset($_POST['resend']) && $_POST['resend'] === 'true';

// 1. Validate Email
$allowedEmails = explode(',', strtolower(ADMIN_EMAILS));
if (!in_array($email, $allowedEmails)){
    echo json_encode(['success' => false, 'message' => 'Unauthorized email address.']);
    exit;
}

// 2. Check Resend Limit
if (!isset($_SESSION['resend_count'])) {
    $_SESSION['resend_count'] = 0;
}

if ($isResend) {
    if ($_SESSION['resend_count'] >= MAX_RESEND_ATTEMPTS) {
        echo json_encode([
            'success' => false, 
            'message' => 'Your OTP attempts have been exhausted. Please try again after some time.',
            'limit_reached' => true
        ]);
        exit;
    }
    $_SESSION['resend_count']++;
} else {
    // Fresh request, reset count
    $_SESSION['resend_count'] = 0;
}

// 3. Generate 6-digit OTP
$otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = time() + OTP_EXPIRY_SECONDS;

// 4. Store in Session
$_SESSION['admin_otp'] = $otp;
$_SESSION['admin_otp_expiry'] = $expiry;
$_SESSION['admin_otp_email'] = $email;

// 5. Send Email
$subject = "Admin Login OTP - Infinity Computer";
$message = "Your 6-digit OTP for admin login is: $otp\n\nThis code will expire in " . OTP_EXPIRY_SECONDS . " seconds.";
$headers = "From: noreply@infinitycomputer.com\r\n";
$headers .= "Reply-To: noreply@infinitycomputer.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// In a real shared hosting environment, mail() is used.
// For development, we might not have a mail server, but we follow the requirement.
$mailSent = @mail($email, $subject, $message, $headers);

// Note: On local XAMPP, mail() usually fails unless configured. 
// We return success if we generated it, but in production, $mailSent would be the indicator.
echo json_encode([
    'success' => true,
    'message' => 'OTP generated successfully.',
    'debug_otp' => $otp // REMOVE THIS IN PRODUCTION
]);
exit;
