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
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-light btn-sm text-info" onclick='viewStudent(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)' title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm text-primary" onclick='editStudent(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)' title="Edit Details">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm text-danger" onclick="deleteStudent(<?php echo $row['id']; ?>)" title="Delete Student">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <?php if($status == 'Pending'): ?>
                                            <button onclick="processAction(<?php echo $row['id']; ?>, 'Successful')" class="btn btn-verify btn-sm rounded-pill px-2" title="Verify">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button onclick="processAction(<?php echo $row['id']; ?>, 'Cancelled')" class="btn btn-reject btn-sm rounded-pill px-2" title="Reject">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Full Name *</label>
                                <input type="text" name="name" id="f_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Father's Name *</label>
                                <input type="text" name="father_name" id="f_father" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Mother's Name *</label>
                                <input type="text" name="mother_name" id="f_mother" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Gender *</label>
                                <select name="gender" id="f_gender" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Date of Birth *</label>
                                <input type="date" name="dob" id="f_dob" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Phone *</label>
                                <input type="text" name="phone" id="f_phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email *</label>
                                <input type="email" name="email" id="f_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Highest Qualification *</label>
                                <input type="text" name="qualification" id="f_qualification" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Address *</label>
                                <textarea name="address" id="f_address" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">City *</label>
                                <input type="text" name="city" id="f_city" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">District *</label>
                                <input type="text" name="district" id="f_district" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Pincode *</label>
                                <input type="text" name="pincode" id="f_pincode" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Course *</label>
                                <select name="course" id="f_course" class="form-select" required>
                                    <?php foreach($courses as $c): ?>
                                        <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Time Slot *</label>
                                <select name="time_slot" id="f_timeslot" class="form-select" required>
                                    <option value="">Select your slot</option>
                                    <option value="10:00 - 11:00 AM" disabled class="bg-light text-muted">10:00 - 11:00 AM (Full)</option>
                                    <option value="11:00 - 12:00 PM" disabled class="bg-light text-muted">11:00 - 12:00 PM (Full)</option>
                                    <option value="12:00 - 01:00 PM" disabled class="bg-light text-muted">12:00 - 01:00 PM (Full)</option>
                                    <option value="01:00 - 02:00 PM">01:00 - 02:00 PM</option>
                                    <option value="02:00 - 03:00 PM">02:00 - 03:00 PM</option>
                                    <option value="03:00 - 04:00 PM">03:00 - 04:00 PM</option>
                                    <option value="04:00 - 05:00 PM">04:00 - 05:00 PM</option>
                                    <option value="05:00 - 06:00 PM">05:00 - 06:00 PM</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Joining Date *</label>
                                <input type="date" name="joining_date" id="f_joining" class="form-control" required onchange="calculateDashEndDate()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-success">End Date <small>(Auto-calc)</small></label>
                                <input type="text" id="v_form_end_date" class="form-control bg-light text-success fw-bold border-success" readonly placeholder="Calculates automatically">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Student Photo * <small class="text-muted fw-normal">(Optional on update)</small></label>
                                <input type="file" name="photo" id="f_photo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ID Proof PDF * <small class="text-muted fw-normal">(Optional on update)</small></label>
                                <input type="file" name="id_proof" id="f_id_proof" class="form-control" accept=".pdf">
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

    <!-- View Student Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Student Details <span id="v_student_id" class="text-muted ms-2 fw-normal" style="font-size: 0.9rem;"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-4 align-items-center">
                        <div class="col-auto">
                            <img id="v_photo" src="../images/placeholder.jpg" class="rounded border" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <div class="col">
                            <h3 id="v_name" class="mb-1 text-primary fw-bold"></h3>
                            <p class="mb-0 text-muted"><i class="fa-solid fa-graduation-cap me-2"></i><span id="v_course"></span> - <span id="v_timeslot"></span></p>
                            <p class="mb-0 text-muted"><i class="fa-solid fa-clock me-2"></i>Status: <strong id="v_status" class="text-dark"></strong> &nbsp;|&nbsp; Joined: <span id="v_joining"></span></p>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Personal Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr><th class="text-muted" style="width: 140px;">Father's Name:</th><td id="v_father"></td></tr>
                                <tr><th class="text-muted">Mother's Name:</th><td id="v_mother"></td></tr>
                                <tr><th class="text-muted">Gender:</th><td id="v_gender"></td></tr>
                                <tr><th class="text-muted">DOB:</th><td id="v_dob"></td></tr>
                                <tr><th class="text-muted">Qualification:</th><td id="v_qualification"></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Contact & Location</h6>
                            <table class="table table-sm table-borderless">
                                <tr><th class="text-muted" style="width: 100px;">Phone:</th><td id="v_phone"></td></tr>
                                <tr><th class="text-muted">Email:</th><td id="v_email"></td></tr>
                                <tr><th class="text-muted">Address:</th><td id="v_address"></td></tr>
                                <tr><th class="text-muted">City / Dist:</th><td><span id="v_city"></span>, <span id="v_district"></span></td></tr>
                                <tr><th class="text-muted">Pincode:</th><td id="v_pincode"></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <a href="#" id="v_idproof_link" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-file-pdf me-2"></i>View ID Document
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
        const previewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        const viewModal = new bootstrap.Modal(document.getElementById('viewStudentModal'));

        function previewImage(src) {
            document.getElementById('previewImg').src = src;
            previewModal.show();
        }

        function resetForm() {
            document.getElementById('studentForm').reset();
            document.getElementById('student_db_id').value = '';
            document.getElementById('f_photo').required = true;
            document.getElementById('f_id_proof').required = true;
            document.getElementById('v_form_end_date').value = '';
            
            // Dynamic date limits identical to enrollment
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const jInput = document.getElementById('f_joining');
            jInput.min = tomorrow.toISOString().split('T')[0];
            const maxFutureDate = new Date(today.getFullYear() + 2, today.getMonth(), today.getDate());
            jInput.max = maxFutureDate.toISOString().split('T')[0];
            
            const dobInput = document.getElementById('f_dob');
            const minAgeDate = new Date(today.getFullYear() - 7, today.getMonth(), today.getDate());
            dobInput.max = minAgeDate.toISOString().split('T')[0];
            const oldestAgeDate = new Date(today.getFullYear() - 70, today.getMonth(), today.getDate());
            dobInput.min = oldestAgeDate.toISOString().split('T')[0];
        }

        const courseDurations = {
            "TALLY": 45, "DTP": 45, "BASIC": 45, "Web Designing": 45,
            "C Programming": 45, "C++ Programming": 45, "Hardware Networking": 180,
            "Python Level 1": 45, "ADVANCE TALLY": 30, "ADVANCE Excel": 15,
            "Advance Word": 15, "ADVANCE BASIC": 60
        };

        function calculateDashEndDate() {
            const jDate = document.getElementById('f_joining').value;
            const course = document.getElementById('f_course').value;
            const endDisp = document.getElementById('v_form_end_date');
            if(jDate && course && courseDurations[course]) {
                const date = new Date(jDate);
                date.setDate(date.getDate() + courseDurations[course]);
                const options = { day: 'numeric', month: 'short', year: 'numeric' };
                endDisp.value = date.toLocaleDateString('en-GB', options);
            } else {
                endDisp.value = "";
            }
        }
        
        document.getElementById('f_course').addEventListener('change', calculateDashEndDate);

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
            document.getElementById('f_father').value = data.father_name || '';
            document.getElementById('f_mother').value = data.mother_name || '';
            
            // Robust gender selection
            const targetGender = (data.gender || '').toLowerCase().trim();
            const gSel = document.getElementById('f_gender');
            for(let i=0; i<gSel.options.length; i++) {
                if(gSel.options[i].value.toLowerCase().trim() === targetGender) {
                    gSel.selectedIndex = i; break;
                }
            }
            
            document.getElementById('f_dob').value = data.dob || '';
            document.getElementById('f_email').value = data.email || '';
            document.getElementById('f_phone').value = data.phone || '';
            document.getElementById('f_qualification').value = data.qualification || '';
            document.getElementById('f_address').value = data.address || '';
            document.getElementById('f_city').value = data.city || '';
            document.getElementById('f_district').value = data.district || '';
            document.getElementById('f_pincode').value = data.pincode || '';
            
            // Robust course selection
            const targetCourse = (data.course || '').toLowerCase().trim();
            const cSel = document.getElementById('f_course');
            for(let i=0; i<cSel.options.length; i++) {
                if(cSel.options[i].value.toLowerCase().trim() === targetCourse) {
                    cSel.selectedIndex = i; break;
                }
            }

            // Robust time_slot selection
            const targetSlot = (data.time_slot || '').toLowerCase().trim();
            const tSel = document.getElementById('f_timeslot');
            for(let i=0; i<tSel.options.length; i++) {
                if(tSel.options[i].value.toLowerCase().trim() === targetSlot || tSel.options[i].value.toLowerCase().trim().startsWith(targetSlot)) {
                    // Try to map exactly. If disabled, JS will select it anyways (admin override)
                    tSel.selectedIndex = i; break;
                }
            }

            document.getElementById('f_joining').removeAttribute('min');
            document.getElementById('f_joining').removeAttribute('max');
            document.getElementById('f_joining').value = data.joining_date || '';
            
            document.getElementById('f_photo').required = false;
            document.getElementById('f_id_proof').required = false;
            
            calculateDashEndDate();
            studentModal.show();
        }

        function viewStudent(data) {
            document.getElementById('v_student_id').textContent = '(' + (data.student_id ? data.student_id : 'Pending ID') + ')';
            document.getElementById('v_name').textContent = data.name;
            document.getElementById('v_course').textContent = data.course;
            document.getElementById('v_timeslot').textContent = data.time_slot || 'Not set';
            document.getElementById('v_status').textContent = data.verification;
            document.getElementById('v_joining').textContent = data.joining_date || 'N/A';
            
            document.getElementById('v_father').textContent = data.father_name || 'N/A';
            document.getElementById('v_mother').textContent = data.mother_name || 'N/A';
            document.getElementById('v_gender').textContent = data.gender || 'N/A';
            document.getElementById('v_dob').textContent = data.dob || 'N/A';
            document.getElementById('v_qualification').textContent = data.qualification || 'N/A';
            
            document.getElementById('v_phone').textContent = data.phone || 'N/A';
            document.getElementById('v_email').textContent = data.email || 'N/A';
            document.getElementById('v_address').textContent = data.address || 'N/A';
            document.getElementById('v_city').textContent = data.city || 'N/A';
            document.getElementById('v_district').textContent = data.district || 'N/A';
            document.getElementById('v_pincode').textContent = data.pincode || 'N/A';
            
            document.getElementById('v_photo').src = (data.photo_path && data.photo_path.trim() !== '') ? '../' + data.photo_path : '../images/placeholder.jpg';
            
            const docLink = document.getElementById('v_idproof_link');
            if(data.id_proof_path && data.id_proof_path.trim() !== '') {
                docLink.href = '../' + data.id_proof_path;
                docLink.style.display = 'inline-block';
            } else {
                docLink.style.display = 'none';
            }
            
            viewModal.show();
        }

        document.getElementById('studentForm').onsubmit = async (e) => {
            e.preventDefault();
            
            const isAdding = !document.getElementById('student_db_id').value;
            const dobInput = document.getElementById('f_dob');
            const joiningDateInput = document.getElementById('f_joining');
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Cross-verify Age
            const dob = new Date(dobInput.value);
            const ageLimit = new Date(today.getFullYear() - 7, today.getMonth(), today.getDate());
            if (dob > ageLimit) {
                alert("Student must be at least 7 years old to enroll.");
                dobInput.focus();
                return;
            }

            // Cross-verify joining date for NEW students only
            if (isAdding) {
                const joiningDate = new Date(joiningDateInput.value);
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                if (joiningDate < tomorrow) {
                    alert("Joining date must be a future date (starting from tomorrow).");
                    joiningDateInput.focus();
                    return;
                }
            }
            
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
