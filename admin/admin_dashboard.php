<?php
require_once 'config.php';
checkAdminLogin();

// Pagination Settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search Query
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$searchQuery = " WHERE verification = 'Successful' ";
if (!empty($search)) {
    $searchQuery .= " AND (name LIKE ? OR phone LIKE ? OR student_id LIKE ?) ";
}

// Get Total Records for Pagination
$totalSql = "SELECT COUNT(*) FROM students" . $searchQuery;
$totalStmt = $conn->prepare($totalSql);
if (!empty($search)) {
    $searchTerm = "%$search%";
    $totalStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
}
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRows = $totalResult->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

// Fetch Student Records
$sql = "SELECT * FROM students" . $searchQuery . " ORDER BY enrollment_date DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bind_param("ssiii", $searchTerm, $searchTerm, $searchTerm, $start, $limit);
} else {
    $stmt->bind_param("ii", $start, $limit);
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
    <title>Admin Dashboard - Infinity Computer</title>
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

        .card-header {
            background-color: var(--secondary-color) !important;
            color: #fff !important;
            font-weight: 600;
            padding: 1.25rem;
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
            box-shadow: 0 4px 12px rgba(251, 42, 113, 0.2);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
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

        .pagination .page-link {
            color: var(--secondary-color);
            border: none;
            margin: 0 3px;
            border-radius: 8px;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                <a href="verification_management.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">
                    <i class="fa-solid fa-user-check me-1"></i> Verification Management
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
            <h4 class="fw-bold mb-0">Student Management</h4>
            <div class="search-box position-relative">
                <form action="" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control rounded-pill px-4" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Course Info</th>
                            <th>Location</th>
                            <th>Dates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><span class="fw-bold"><?php echo $row['student_id']; ?></span></td>
                                <td>
                                    <?php if($row['photo_path']): ?>
                                        <img src="../<?php echo $row['photo_path']; ?>" class="student-photo" onclick="previewImage('../<?php echo $row['photo_path']; ?>')">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($row['name']); ?></div>
                                    <small class="text-muted">F: <?php echo htmlspecialchars($row['father_name'] ?? '-'); ?></small>
                                </td>
                                <td>
                                    <div class="small"><i class="fa-solid fa-phone me-1 text-muted"></i><?php echo $row['phone']; ?></div>
                                    <div class="small"><i class="fa-solid fa-envelope me-1 text-muted"></i><?php echo $row['email']; ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2"><?php echo $row['course']; ?></span><br>
                                    <small class="text-muted"><?php echo $row['time_slot']; ?></small>
                                </td>
                                <td>
                                    <div class="small"><?php echo htmlspecialchars($row['city'] ?? ''); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($row['district'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <div class="small text-success">J: <?php echo $row['joining_date'] ?? '-'; ?></div>
                                    <div class="small text-danger">E: <?php echo $row['end_date'] ?? '-'; ?></div>
                                </td>
                                <td>
                                    <button class="btn btn-light btn-action text-primary" onclick="editStudent(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn btn-light btn-action text-danger" onclick="deleteStudent(<?php echo $row['id']; ?>)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <a href="../<?php echo $row['id_proof_path']; ?>" target="_blank" class="btn btn-light btn-action text-info">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
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
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Student Add/Edit Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Student</h5>
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
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mother's Name</label>
                                <input type="text" name="mother_name" id="f_mother" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Gender</label>
                                <select name="gender" id="f_gender" class="form-select">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">DOB</label>
                                <input type="date" name="dob" id="f_dob" class="form-control">
                            </div>
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
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Time Slot</label>
                                <select name="time_slot" id="f_timeslot" class="form-select">
                                    <option value="10:00 - 11:00 AM">10:00 - 11:00 AM</option>
                                    <option value="11:00 - 12:00 PM">11:00 - 12:00 PM</option>
                                    <option value="12:00 - 01:00 PM">12:00 - 01:00 PM</option>
                                    <option value="01:00 - 02:00 PM">01:00 - 02:00 PM</option>
                                    <option value="02:00 - 03:00 PM">02:00 - 03:00 PM</option>
                                    <option value="03:00 - 04:00 PM">03:00 - 04:00 PM</option>
                                    <option value="04:00 - 05:00 PM">04:00 - 05:00 PM</option>
                                    <option value="05:00 - 06:00 PM">05:00 - 06:00 PM</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Joining Date</label>
                                <input type="date" name="joining_date" id="f_joining" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Qualification</label>
                                <input type="text" name="qualification" id="f_qualification" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Student Photo</label>
                                <input type="file" name="photo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ID Proof (PDF)</label>
                                <input type="file" name="id_proof" class="form-control">
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
                <div class="modal-body">
                    <img id="previewImg" src="" alt="Preview">
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
            document.getElementById('modalTitle').textContent = 'Add New Student';
        }

        function editStudent(data) {
            resetForm();
            document.getElementById('modalTitle').textContent = 'Edit Student: ' + data.student_id;
            document.getElementById('student_db_id').value = data.id;
            document.getElementById('f_name').value = data.name;
            document.getElementById('f_father').value = data.father_name || '';
            document.getElementById('f_mother').value = data.mother_name || '';
            document.getElementById('f_gender').value = data.gender || 'male';
            document.getElementById('f_dob').value = data.dob || '';
            document.getElementById('f_email').value = data.email || '';
            document.getElementById('f_phone').value = data.phone || '';
            document.getElementById('f_address').value = data.address || '';
            document.getElementById('f_city').value = data.city || '';
            document.getElementById('f_district').value = data.district || '';
            document.getElementById('f_pincode').value = data.pincode || '';
            document.getElementById('f_course').value = data.course;
            document.getElementById('f_timeslot').value = data.time_slot;
            document.getElementById('f_joining').value = data.joining_date || '';
            document.getElementById('f_qualification').value = data.qualification || '';
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
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('An error occurred during save.');
            }
        };

        async function deleteStudent(id) {
            if (!confirm('Are you sure you want to delete this student record?')) return;
            
            try {
                const response = await fetch('delete_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('An error occurred during deletion.');
            }
        }
    </script>
</body>
</html>
