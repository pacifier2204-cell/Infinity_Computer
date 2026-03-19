<?php
require_once 'config.php';
checkAdminLogin();

// Fetch Pending Students
$sql = "SELECT * FROM students WHERE verification = 'Pending' ORDER BY enrollment_date DESC";
$result = $conn->query($sql);

$courses = ["TALLY", "DTP", "BASIC", "Web Designing", "C Programming", "C++ Programming", "Hardware Networking", "Python Level 1", "ADVANCE TALLY", "ADVANCE Excel", "Advance Word", "ADVANCE BASIC"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Management - Infinity Computer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #fb2a71;
            --primary-hover: #ff0000;
            --secondary-color: #111111;
            --main-bg: #f8fafc;
            --card-shadow: 0 4px 15px rgba(251, 42, 113, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--main-bg);
            color: #334155;
        }

        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 3px solid var(--primary-color);
        }

        .dashboard-container {
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8fafc;
            color: var(--secondary-color);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
            padding: 1rem;
        }

        .student-photo {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #edf2f7;
        }

        .btn-verify { 
            background-color: #10b981; 
            color: white; 
            border: none;
            transition: all 0.3s ease;
        }
        .btn-verify:hover { 
            background-color: #059669; 
            color: white; 
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }
        .btn-reject { 
            background-color: #ef4444; 
            color: white; 
            border: none;
            transition: all 0.3s ease;
        }
        .btn-reject:hover { 
            background-color: #dc2626; 
            color: white; 
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }
        .doc-link { 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 600; 
        }
        .doc-link:hover { 
            color: var(--primary-hover);
            text-decoration: underline; 
        }

        .brand-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(90deg, #fb2a71 0%, #ff0000 50%, #fb2a71 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #fb2a71;
            white-space: nowrap;
            line-height: 1;
            display: inline-flex;
            align-items: baseline;
            margin-left: 8px;
        }

        .text-accent {
            background: linear-gradient(135deg, #111111 0%, #333333 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #111111;
            font-weight: 700;
            margin-left: 2px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand ms-3 d-flex align-items-center text-decoration-none" href="admin_dashboard.php">
                <img src="../images/logos/infinity_computer_logo.png" alt="Infinity Computer Logo" class="brand-logo">
                <span class="brand-text">Infinity<span class="text-accent">Computer</span></span>
            </a>
            <div class="d-flex align-items-center">
                <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid dashboard-container">
        <h4 class="fw-bold mb-4">Verification Management <span class="badge bg-warning text-dark ms-2" style="font-size: 0.5em; vertical-align: middle;">Pending Review</span></h4>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Student Details</th>
                            <th>Contact & DOB</th>
                            <th>Course Info</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($row['photo_path']): ?>
                                                <img src="../<?php echo $row['photo_path']; ?>" class="student-photo me-3">
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></div>
                                                <small class="text-muted">SID: <?php echo $row['student_id']; ?></small><br>
                                                <small class="text-muted">Father: <?php echo htmlspecialchars($row['father_name']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small"><i class="fa-solid fa-phone me-1 text-muted"></i><?php echo $row['phone']; ?></div>
                                        <div class="small"><i class="fa-solid fa-envelope me-1 text-muted"></i><?php echo $row['email']; ?></div>
                                        <div class="small mt-1"><i class="fa-solid fa-calendar me-1 text-muted"></i>DOB: <?php echo date('d M, Y', strtotime($row['dob'])); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2"><?php echo $row['course']; ?></span><br>
                                        <small class="text-muted">Applied: <?php echo date('d M, Y', strtotime($row['enrollment_date'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="../<?php echo $row['id_proof_path']; ?>" target="_blank" class="doc-link small">
                                            <i class="fa-solid fa-file-pdf me-1"></i> View ID Proof
                                        </a>
                                        <br>
                                        <a href="../<?php echo $row['photo_path']; ?>" target="_blank" class="doc-link small">
                                            <i class="fa-solid fa-image me-1"></i> View Photo
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button onclick="processAction(<?php echo $row['id']; ?>, 'Successful')" class="btn btn-verify btn-sm rounded-pill px-3">
                                                <i class="fa-solid fa-check me-1"></i> Verify
                                            </button>
                                            <button onclick="processAction(<?php echo $row['id']; ?>, 'Cancelled')" class="btn btn-reject btn-sm rounded-pill px-3">
                                                <i class="fa-solid fa-times me-1"></i> Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No pending enrollment requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function processAction(id, status) {
            const actionText = status === 'Successful' ? 'verify' : 'reject';
            if (!confirm(`Are you sure you want to ${actionText} this enrollment?`)) return;

            try {
                const response = await fetch('process_verification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&status=${status}`
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('An error occurred during processing.');
            }
        }
    </script>
</body>
</html>
