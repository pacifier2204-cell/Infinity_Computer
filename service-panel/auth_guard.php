<?php
/**
 * Auth Guard — Auto-prepended to all .html files in service-panel via .htaccess
 * Protects pages by requiring a valid staff session.
 * Skips protection for login.html and all .php API files.
 */

$currentFile = basename($_SERVER['SCRIPT_FILENAME']);
$ext = pathinfo($currentFile, PATHINFO_EXTENSION);

// Only protect HTML files — do not interfere with PHP API endpoints
if ($ext !== 'html') return;

// Allow the login page through without auth
if ($currentFile === 'login.html') return;

session_start();

$sessionTimeout = 15 * 60; // 15 minutes

// Check if authenticated
if (!isset($_SESSION['staff_authenticated']) || $_SESSION['staff_authenticated'] !== true) {
    header('Location: login.html');
    exit;
}

// Check session timeout
if (isset($_SESSION['staff_login_time']) && (time() - $_SESSION['staff_login_time']) > $sessionTimeout) {
    session_unset();
    session_destroy();
    header('Location: login.html?expired=1');
    exit;
}

// Update last activity
$_SESSION['staff_last_activity'] = time();

// Inject client-side auto-redirect timer for session expiry
$remainingMs = ($sessionTimeout - (time() - $_SESSION['staff_login_time'])) * 1000;
if ($remainingMs > 0) {
    echo "<script>setTimeout(function(){window.location.href='login.html?expired=1';}," . $remainingMs . ");</script>\n";
}
?>
