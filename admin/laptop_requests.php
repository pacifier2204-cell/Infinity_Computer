<?php
require_once 'config.php';
checkAdminLogin();

$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';
$sql = "SELECT * FROM second_hand_laptop_requests WHERE status = ? ORDER BY submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptop Buyback Requests - Infinity Computer</title>
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

        .nav-tabs {
            border-bottom: none;
            gap: 10px;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            color: #64748b;
            font-weight: 600;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: #fff;
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

        .laptop-thumb {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #edf2f7;
            margin-right: 5px;
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
        }
        .doc-link { 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 600; 
        }

        .brand-logo {
            height: 40px;
            width: auto;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(90deg, #fb2a71 0%, #ff0000 50%, #fb2a71 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-left: 8px;
        }

        .text-accent {
            background: linear-gradient(135deg, #111111 0%, #333333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand ms-3 d-flex align-items-center text-decoration-none" href="admin_dashboard.php">
                <img src="../images/logos/infinity_computer_logo.png" alt="Logo" class="brand-logo">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Second Hand Laptop Requests</h4>
            <div class="badge bg-dark rounded-pill px-3 py-2">System Version 2.1</div>
        </div>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $status == 'Pending' ? 'active' : ''; ?>" href="?status=Pending">
                    <i class="fa-solid fa-clock me-2"></i>Pending
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status == 'Verified' ? 'active' : ''; ?>" href="?status=Verified">
                    <i class="fa-solid fa-check-circle me-2"></i>Verified
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status == 'Rejected' ? 'active' : ''; ?>" href="?status=Rejected">
                    <i class="fa-solid fa-times-circle me-2"></i>Rejected
                </a>
            </li>
        </ul>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Owner Details</th>
                            <th>Laptop Info</th>
                            <th>Price & Specs</th>
                            <th>Documents/Photos</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['owner_name']); ?></div>
                                        <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i><?php echo $row['mobile']; ?></div>
                                        <div class="small text-muted"><i class="fa-solid fa-envelope me-1"></i><?php echo htmlspecialchars($row['email']); ?></div>
                                        <div class="small text-muted mt-1"><i class="fa-solid fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($row['address']); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['laptop_company']); ?></div>
                                        <div class="small">Model: <?php echo htmlspecialchars($row['laptop_model']); ?></div>
                                        <div class="small text-muted">SN: <?php echo htmlspecialchars($row['serial_number']); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success">₹<?php echo number_get_formatted_price($row['expected_price']); ?></div>
                                        <div class="small text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($row['description']); ?></div>
                                        <small class="text-muted"><?php echo date('d M, Y', strtotime($row['submitted_at'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="../<?php echo $row['document_path']; ?>" target="_blank" class="doc-link small d-block mb-2">
                                            <i class="fa-solid fa-file-pdf me-1"></i> Address Proof
                                        </a>
                                        <div class="d-flex">
                                            <?php 
                                            $images = json_decode($row['laptop_images'], true);
                                            if ($images && is_array($images)) {
                                                foreach (array_slice($images, 0, 3) as $img) {
                                                    echo '<a href="../' . $img . '" target="_blank"><img src="../' . $img . '" class="laptop-thumb"></a>';
                                                }
                                                if (count($images) > 3) echo '<small class="text-muted align-self-end">+'.(count($images)-3).'</small>';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <div class="d-flex gap-2">
                                                <button onclick="updateStatus(<?php echo $row['id']; ?>, 'Verified')" class="btn btn-verify btn-sm rounded-pill px-3">
                                                    <i class="fa-solid fa-check me-1"></i> Verify
                                                </button>
                                                <button onclick="updateStatus(<?php echo $row['id']; ?>, 'Rejected')" class="btn btn-reject btn-sm rounded-pill px-3">
                                                    <i class="fa-solid fa-times me-1"></i> Reject
                                                </button>
                                            </div>
                                        <?php elseif ($row['status'] == 'Verified'): ?>
                                            <span class="badge bg-success rounded-pill">Verified on <?php echo date('d M', strtotime($row['verified_at'])); ?></span>
                                            <button onclick="deleteRequest(<?php echo $row['id']; ?>)" class="btn btn-link btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No <?php echo strtolower($status); ?> requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
    function number_get_formatted_price($number) {
        return number_format($number, 2);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function updateStatus(id, status) {
            const action = status === 'Verified' ? 'verify and notify the user' : 'reject';
            if (!confirm(`Are you sure you want to ${action} this request?`)) return;

            try {
                const response = await fetch('process_laptop_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&status=${status}`
                });
                const result = await response.json();
                if (result.success) {
                    let msg = result.message;
                    // Notify admin if email delivery failed
                    if (result.email_status === 'failed') {
                        msg += '\n\n⚠️ EMAIL WARNING: ' + (result.email_warning || 'Email notification could not be sent to the user.');
                    } else {
                        msg += '\n\n✅ Email notification sent successfully.';
                    }
                    alert(msg);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('An error occurred during processing.');
            }
        }

        async function deleteRequest(id) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) return;
            // Implementation for delete can be added if needed
            alert('Delete functionality coming soon or handle via process_laptop_action.php');
        }
    </script>
</body>
</html>
