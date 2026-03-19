<?php
require_once 'config.php';

// Email details
$to = "rathorjatin70@gmail.com";
$subject = "Local Server Mail Test";
$message = "Hello Admin,

This is a test email sent using PHP mail() from your local server.

Time: " . date("Y-m-d H:i:s") . "

If you received this email, mail() is working correctly.";

$headers = "From: test@infinitycomputer.in\r\n";
$headers .= "Reply-To: test@localhost\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
if(mail($to, $subject, $message, $headers)){
    echo "<h3 style='color:green;'>Email sent successfully to $to</h3>";
}else{
    echo "<h3 style='color:red;'>Email sending failed.</h3>";
}
?>