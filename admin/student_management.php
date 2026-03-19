<?php
require_once 'config.php';
checkAdminLogin();

$status = isset($_GET['status']) ? $_GET['status'] : 'Successful';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Pagination Settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Base Query
$whereClause = " WHERE verification = ? ";
if (!empty($search)) {
    $whereClause .= " AND (name LIKE ? OR phone LIKE ? OR student_id LIKE ?) ";
}

// Get Total Records for Pagination
$totalSql = "SELECT COUNT(*) FROM students" . $whereClause;
$totalStmt = $conn->prepare($totalSql);
if (!empty($search)) {
    $searchTerm = "%$search%";
    $totalStmt->bind_param("ssss", $status, $searchTerm, $searchTerm, $searchTerm);
} else {
    $totalStmt->bind_param("s", $status);
}
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRows = $totalResult->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

// Fetch Student Records
$sql = "SELECT * FROM students" . $whereClause . " ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bind_param("ssssii", $status, $searchTerm, $searchTerm, $searchTerm, $start, $limit);
} else {
    $stmt->bind_param("sii", $status, $start, $limit);
}
$stmt->execute();
$result = $stmt->get_result();

// Course Options for Modals
$courses = ["TALLY", "DTP", "BASIC", "Web Designing", "C Programming", "C++ Programming", "Hardware Networking", "Python Level 1", "ADVANCE TALLY", "ADVANCE Excel", "Advance Word", "ADVANCE BASIC"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Infinity Computer</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
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

        .table {
            margin-bottom: 0;
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

        .table td {
            vertical-align: middle;
            padding: 1rem;
            border-bottom: 1px solid #edf2f7;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .student-photo {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
            border: 2px solid #edf2f7;
        }

        .student-photo:hover {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .btn-verify { background: #10b981; color: #fff; border: none; }
        .btn-reject { background: #ef4444; color: #fff; border: none; }

        .brand-logo {
            height: 40px;
            width: auto;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(90deg, #fb2a71, #ff0000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand ms-3 d-flex align-items-center text-decoration-none" href="admin_dashboard.php">
                <img src="../images/logos/infinity_computer_logo.png" alt="Infinity Computer Logo" class="brand-logo">
                <span class="brand-text ms-2">Infinity<span style="-webkit-text-fill-color: #111; color: #111;">Computer</span></span>
            </a>
            <div class="d-flex align-items-center">
                <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2">
                    <i class="fa-solid fa-house me-1"></i> Dashboard
                </a>
                <button class="btn btn-primary btn-sm rounded-pill px-3 me-3" data-bs-toggle="modal" data-bs-target="#studentModal" onclick="resetForm()">
                    <i class="fa-solid fa-plus me-1"></i> Add Student
                </button>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid dashboard-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Student System</h4>
            <div class="search-box position-relative">
                <form action="" method="GET" class="d-flex">
                    <input type="hidden" name="status" value="<?php echo $status; ?>">
                    <input type="text" name="search" class="form-control rounded-pill px-4" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>
        </div>

        <!-- Subtabs -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $status == 'Pending' ? 'active' : ''; ?>" href="?status=Pending">
                    <i class="fa-solid fa-clock me-2"></i>Pending
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status == 'Successful' ? 'active' : ''; ?>" href="?status=Successful">
                    <i class="fa-solid fa-check-circle me-2"></i>Verified
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status == 'Cancelled' ? 'active' : ''; ?>" href="?status=Cancelled">
                    <i class="fa-solid fa-times-circle me-2"></i>Cancelled
                </a>
            </li>
        </ul>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Contact & DOB</th>
                            <th>Course Info</th>
                            <th>Location/Docs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if($row['photo_path']): ?>
                                            <img src="../<?php echo $row['photo_path']; ?>" class="student-photo me-3" onclick="previewImage('../<?php echo $row['photo_path']; ?>')">
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <small class="text-muted"><?php echo $row['student_id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><i class="fa-solid fa-phone me-1 text-muted"></i><?php echo $row['phone']; ?></div>
                                    <div class="small"><i class="fa-solid fa-calendar me-1 text-muted"></i><?php echo $row['dob']; ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2"><?php echo $row['course']; ?></span><br>
                                    <small class="text-muted"><?php echo $row['time_slot']; ?></small>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($row['city'] ?? ''); ?></div>
                                    <a href="../<?php echo $row['id_proof_path']; ?>" target="_blank" class="small text-primary text-decoration-none">
                                        <i class="fa-solid fa-file-pdf me-1"></i> ID Proof
                                    </a>
                                </td>
                                <td>
                                    <?php if($status == 'Pending'): ?>
                                        <div class="d-flex gap-2">
                                            <button onclick="processAction(<?php echo $row['id']; ?>, 'Successful')" class="btn btn-verify btn-sm rounded-pill px-3">
                                                Verify
                                            </button>
                                            <button onclick="processAction(<?php echo $row['id']; ?>, 'Cancelled')" class="btn btn-reject btn-sm rounded-pill px-3">
                                                Reject
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-light btn-sm text-primary" onclick='editStudent(<?php echo json_encode($row); ?>)'>
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="btn btn-light btn-sm text-danger" onclick="deleteStudent(<?php echo $row['id']; ?>)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($result->num_rows == 0): ?>
                            <tr><td colspan="5" class="text-center py-5">No records found for "<?php echo $status; ?>".</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Student Add/Edit Modal (Existing) -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="studentForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="student_db_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name *</label>
                                <input type="text" name="name" id="f_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Father's Name</label>
                                <input type="text" name="father_name" id="f_father" class="form-control">
                            </div>
                            <!-- ... (Rest of fields from previous version) ... -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" name="email" id="f_email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone *</label>
                                <input type="text" name="phone" id="f_phone" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Address</label>
                                <textarea name="address" id="f_address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">City</label>
                                <input type="text" name="city" id="f_city" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">District</label>
                                <input type="text" name="district" id="f_district" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Pincode</label>
                                <input type="text" name="pincode" id="f_pincode" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Course *</label>
                                <select name="course" id="f_course" class="form-select" required>
                                    <?php foreach($courses as $c): ?>
                                        <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content overflow-hidden">
                <div class="modal-body p-0">
                    <img id="previewImg" src="" alt="Preview" style="width: 100%; height: auto;">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
        const previewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));

        function previewImage(src) {
            document.getElementById('previewImg').src = src;
            previewModal.show();
        }

        function resetForm() {
            document.getElementById('studentForm').reset();
            document.getElementById('student_db_id').value = '';
        }

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
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('An error occurred during processing.');
            }
        }

        function editStudent(data) {
            resetForm();
            document.getElementById('student_db_id').value = data.id;
            document.getElementById('f_name').value = data.name;
            document.getElementById('f_email').value = data.email || '';
            document.getElementById('f_phone').value = data.phone || '';
            document.getElementById('f_address').value = data.address || '';
            document.getElementById('f_city').value = data.city || '';
            document.getElementById('f_district').value = data.district || '';
            document.getElementById('f_pincode').value = data.pincode || '';
            document.getElementById('f_course').value = data.course;
            studentModal.show();
        }

        document.getElementById('studentForm').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('save_student.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) location.reload();
                else alert('Error: ' + result.message);
            } catch (err) { alert('An error occurred.'); }
        };

        async function deleteStudent(id) {
            if (!confirm('Are you sure?')) return;
            try {
                const response = await fetch('delete_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                const result = await response.json();
                if (result.success) location.reload();
            } catch (err) { alert('An error occurred.'); }
        }
    </script>
</body>
</html>
