<?php
$studentId = isset($_GET['sid']) ? htmlspecialchars($_GET['sid']) : 'N/A';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enrollment Confirmed | Infinity Computer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .confirmation-container {
      max-width: 700px;
      margin: 60px auto;
      padding: 50px;
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.08);
      text-align: center;
      border: 1px solid #f1f5f9;
    }
    .success-icon {
      width: 100px;
      height: 100px;
      background: #effaf3;
      color: #22c55e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 30px;
      font-size: 40px;
    }
    .sid-box {
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      padding: 25px;
      border-radius: 16px;
      margin: 30px 0;
      position: relative;
    }
    .sid-label {
      font-size: 14px;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
      margin-bottom: 8px;
    }
    .sid-value {
      font-family: 'Outfit', sans-serif;
      font-size: 36px;
      font-weight: 700;
      color: #0d6efd;
      margin: 0;
    }
    .status-note {
      background: #f0f7ff;
      border-left: 4px solid #0d6efd;
      padding: 20px;
      text-align: left;
      border-radius: 8px;
      margin-top: 30px;
    }
    .btn-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 40px;
    }
    .copy-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      transition: color 0.2s;
    }
    .copy-btn:hover { color: #0d6efd; }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="index.html" style="text-decoration:none;">
        <img src="images/logos/infinity_computer_logo.png" alt="Infinity Computer Logo" class="brand-logo">
        <span class="brand-text">Infinity<span class="text-accent">Computer</span></span>
      </a>
      <nav class="main-nav">
        <ul>
          <li><a href="index.html">Home</a></li>
          <li><a href="education.html">Education</a></li>
          
          <li><a class="active" href="enrollment.html">Enrollment</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container">
    <div class="confirmation-container">
      <div class="success-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
      </div>
      
      <h1 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 15px;">Enrollment Successful!</h1>
      <p style="color: #64748b; font-size: 18px; line-height: 1.6;">
        Your enrollment request has been received successfully.<br>
        You will be notified through your registered email after verification shortly.
      </p>

      <div class="sid-box">
        <div class="sid-label">Your Student ID</div>
        <div class="sid-value" id="sidDisplay"><?php echo $studentId; ?></div>
        <button class="copy-btn" title="Copy to clipboard" onclick="copySID()">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </button>
      </div>

      <div class="status-note">
        <strong style="color: #0d6efd; display: block; margin-bottom: 10px;">How to track your status?</strong>
        <p style="margin: 0; color: #475569; font-size: 15px;">
          Please save this ID. You can check your application status anytime using your <strong>Student ID (SID)</strong> and <strong>Date of Birth (DOB)</strong>.
        </p>
      </div>

      <div class="btn-group">
        <a href="enrollment_status.php" class="btn btn-primary" style="padding: 12px 25px; text-decoration: none; border-radius: 10px;">Check Status Now</a>
        <a href="index.html" class="btn btn-outline" style="padding: 12px 25px; text-decoration: none; border-radius: 10px; border: 1px solid #cbd5e1; color: #64748b;">Back to Home</a>
      </div>
    </div>
  </main>

  <script>
    function copySID() {
      const sid = document.getElementById('sidDisplay').innerText;
      navigator.clipboard.writeText(sid).then(() => {
        alert("Student ID copied to clipboard!");
      });
    }
  </script>
</body>
</html>

