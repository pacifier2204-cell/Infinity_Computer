<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data
    $name = strip_tags(trim($_POST["name"] ?? ''));
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST["phone"] ?? ''));
    $topic = strip_tags(trim($_POST["topic"] ?? ''));
    $message = strip_tags(trim($_POST["message"] ?? ''));
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Validation
    $errors = [];
    if (empty($name)) $errors[] = "Name";
    if (empty($email)) $errors[] = "Email";
    if (empty($phone)) $errors[] = "Phone";
    if (empty($topic)) $errors[] = "Interest/Topic";
    if (empty($message)) $errors[] = "Message";
    if (empty($recaptchaResponse)) $errors[] = "reCAPTCHA Verification";

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields: ' . implode(', ', $errors)]);
        exit;
    }

    // reCAPTCHA Secret Key (Same as enroll.php as requested)
    $recaptchaSecret = '6LcadY0sAAAAAE-ADcAzbPWGpJLAdi1oW2jLB4Qe';
    
    // Verify reCAPTCHA
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $responseData = json_decode($verifyResponse);

    if (!$responseData->success) {
        echo json_encode(['success' => false, 'message' => 'Robot verification failed. Please try again.']);
        exit;
    }

    // Send Email
    $to = "icc@infinitycomputer.in";
    $subject = "New Inquiry from Website: $topic";
    
    $email_content = "You have received a new inquiry from the Infinity Computer website.\n\n";
    $email_content .= "Full Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Phone: $phone\n";
    $email_content .= "Interest: $topic\n\n";
    $email_content .= "Message:\n$message\n";

    $headers = "From: noreply@infinitycomputer.in\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $email_content, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'System error: Could not send email. Please try again later.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
