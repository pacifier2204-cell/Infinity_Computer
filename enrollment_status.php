<?php
// This page now simply acts as a portal to search.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Check Enrollment Status | Infinity Computer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
    .status-card {
      max-width: 600px;
      margin: 60px auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      overflow: hidden;
      border: 1px solid #e2e8f0;
    }
    .card-header {
      background: #0d6efd;
      color: #fff;
      padding: 30px;
      text-align: center;
    }
    .card-body { padding: 40px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #475569; }
    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      font-family: inherit;
    }
    .btn-check {
      width: 100%;
      padding: 14px;
      background: #0d6efd;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s;
    }
    .btn-check:hover { background: #0056b3; }
    .result-box {
      margin-top: 30px;
      padding-top: 30px;
      border-top: 1px solid #e2e8f0;
    }
    .status-badge {
      display: inline-block;
      padding: 6px 16px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 14px;
      text-transform: uppercase;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-successful { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .detail-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-size: 15px;
    }
    .detail-label { color: #64748b; }
    .detail-value { font-weight: 600; color: #1e293b; }
    .error-msg {
      background: #fef2f2;
      color: #dc2626;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      text-align: center;
    }
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
          
          <li><a href="enrollment.html">Enrollment</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container">
    <div class="status-card">
      <div class="card-header">
        <h2 style="margin:0; font-family:'Outfit',sans-serif;">Check Enrollment Status</h2>
        <p style="margin:10px 0 0; opacity:0.8;">Enter your details to track your application</p>
      </div>
      <div class="card-body">
        <?php if ($error): ?>
          <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="search.php" method="GET">
          <div class="form-group">
            <label for="sid">Student ID (SID)</label>
            <input type="text" id="sid" name="student_id" class="form-control" placeholder="e.g. ICC0001" required>
          </div>
          <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" class="form-control" required>
          </div>
          <button type="submit" class="btn-check">Check Status</button>
        </form>
      </div>
    </div>
  </main>

  <footer class="site-footer" style="margin-top: 60px;">
    <div class="container footer-bottom">
      <div class="global-presence" style="margin-bottom: 10px; opacity: 0.8;"><i class="fas fa-globe" style="margin-right: 8px; color: #fb2a71;"></i>Our Global Presence: UK | UAE | India</div>
      <small>Copyright 2026 Infinity Computer. All rights reserved.</small>
    </div>
  </footer>
</body>
</html>


