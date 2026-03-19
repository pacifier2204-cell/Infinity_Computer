<?php
require_once 'config/db.php';

$studentId = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
$dob = isset($_GET['dob']) ? $_GET['dob'] : '';

if (empty($studentId) || empty($dob)) {
    die("Please provide both Student ID and Date of Birth.");
}

$sql = "SELECT * FROM students WHERE student_id = ? AND dob = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $studentId, $dob);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enrollment Details | Infinity Computer</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .result-card {
      max-width: 600px;
      margin: 60px auto;
      padding: 40px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #eee;
    }
    .detail-label {
      font-weight: 600;
      color: #6c757d;
    }
    .detail-value {
      font-weight: 700;
      color: #212529;
    }
    .no-result {
      text-align: center;
      padding: 50px;
      color: #dc3545;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="result-card">
      <?php if ($student): ?>
        <h2 style="margin-bottom: 25px; text-align: center; color: #0d6efd;">Enrollment Details</h2>

        <div style="text-align: center; margin-bottom: 30px;">
            <?php if($student['photo_path']): ?>
                <img src="<?php echo htmlspecialchars($student['photo_path']); ?>" alt="Student Photo" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f7ff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 0 auto;">
            <?php else: ?>
                <div style="width: 150px; height: 150px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #94a3b8; border: 4px solid #fff;">
                    <i class="fa-solid fa-user" style="font-size: 50px;"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">Student Name:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['name']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Student ID:</span>
          <span class="detail-value" style="color: #0d6efd;"><?php echo htmlspecialchars($student['student_id']); ?></span>
        </div>

        <div class="detail-row" style="align-items: center; margin: 10px 0; padding: 15px 0;">
            <span class="detail-label">Verification Status:</span>
            <?php
            $statusClass = '';
            $statusIcon = '';
            switch($student['verification']) {
                case 'Successful': $statusClass = 'background: #dcfce7; color: #166534;'; $statusIcon = '✅'; break;
                case 'Cancelled': $statusClass = 'background: #fee2e2; color: #991b1b;'; $statusIcon = '❌'; break;
                default: $statusClass = 'background: #fef3c7; color: #92400e;'; $statusIcon = '⏳'; break;
            }
            ?>
            <span style="padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 14px; <?php echo $statusClass; ?>">
                <?php echo $statusIcon . ' ' . $student['verification']; ?>
            </span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Course:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['course']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Duration:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['duration']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Time Slot:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['time_slot']); ?></span>
        </div>
         <div class="detail-row">
          <span class="detail-label">Joining Date:</span>
          <span class="detail-value"><?php echo $student['joining_date'] ? date('d M, Y', strtotime($student['joining_date'])) : 'N/A'; ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">City:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['city']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">District:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['district']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Pincode:</span>
          <span class="detail-value"><?php echo htmlspecialchars($student['pincode']); ?></span>
        </div>
        
        <div class="detail-row" style="background: #f0fdf4; border-radius: 8px; padding: 15px; border: 1px solid #dcfce7; margin-top: 10px;">
          <span class="detail-label" style="color: #166534;">Expected Course End Date:</span>
          <span class="detail-value" style="color: #166534; font-size: 1.1em;"><?php echo $student['end_date'] ? date('d M, Y', strtotime($student['end_date'])) : 'To Be Determined'; ?></span>
        </div>
        <p style="margin-top: 15px; font-size: 0.9em; color: #dc3545; font-weight: 500; text-align: center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Note: Your Student ID access will expire after <strong><?php echo $student['end_date'] ? date('d M, Y', strtotime($student['end_date'])) : 'completion'; ?></strong>.
        </p>

        <div style="margin-top: 35px; text-align: center; border-top: 1px solid #eee; padding-top: 25px;">
          <a href="search.html" class="btn btn-outline" style="margin-right: 10px; border: 1px solid #dee2e6; color: #666; text-decoration: none; padding: 10px 20px; border-radius: 8px;">← New Search</a>
          <a href="index.html" class="btn btn-primary" style="background: #0d6efd; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px;">Back to Home</a>
        </div>

      <?php else: ?>
        <div class="no-result">
          <h3>No Records Found</h3>
          <p>Please check your Student ID and Date of Birth and try again.</p>
          <a href="search.html" class="btn btn-primary" style="margin-top: 20px;">Try Again</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
