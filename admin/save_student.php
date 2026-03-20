<?php
require_once 'config.php';
checkAdminLogin(); // Temporarily disabled by config.php, but good for future

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

/**
 * Handle File Upload (Reuse logic from enroll.php)
 */
function handleFileUpload($fileKey, $targetDir, $allowedTypes, $maxSize) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => "skip"]; // Skip if no new file uploaded
    }

    $file = $_FILES[$fileKey];
    $originalName = basename($file['name']);
    $tmpName = $file['tmp_name'];
    
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => "Invalid type: $ext"];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => "File too large"];
    }

    if (!is_dir('../' . $targetDir)) {
        mkdir('../' . $targetDir, 0755, true);
    }

    $newName = time() . '_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);
    $destPath = $targetDir . $newName;

    if (move_uploaded_file($tmpName, '../' . $destPath)) {
        return ['success' => true, 'path' => $destPath];
    }

    return ['success' => false, 'error' => "Failed to move file"];
}

// 1. Get Data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = sanitizeInput($_POST['name']);
$father_name = sanitizeInput($_POST['father_name']);
$mother_name = sanitizeInput($_POST['mother_name']);
$gender = sanitizeInput($_POST['gender']);
$dob = sanitizeInput($_POST['dob']);
$email = sanitizeInput($_POST['email']);
$phone = sanitizeInput($_POST['phone']);
$address = sanitizeInput($_POST['address']);
$city = sanitizeInput($_POST['city']);
$district = sanitizeInput($_POST['district']);
$pincode = sanitizeInput($_POST['pincode']);
$course = sanitizeInput($_POST['course']);
$time_slot = sanitizeInput($_POST['time_slot']);
$joining_date = sanitizeInput($_POST['joining_date']);
$qualification = sanitizeInput($_POST['qualification']);

// 2. Calculate Duration and End Date (Logic from enroll.php)
$durations = [
    "TALLY" => "1.5 Months", "DTP" => "1.5 Months", "BASIC" => "1.5 Months",
    "Web Designing" => "1.5 Months", "C Programming" => "1.5 Months",
    "C++ Programming" => "1.5 Months", "Hardware Networking" => "6 Months",
    "Python Level 1" => "1.5 Months", "ADVANCE TALLY" => "1 Month",
    "ADVANCE Excel" => "15 Days", "Advance Word" => "15 Days", "ADVANCE BASIC" => "2 Months"
];
$duration = $durations[$course] ?? "Varies";
$start = new DateTime($joining_date);
if ($duration === "1.5 Months") $start->modify('+45 days');
elseif ($duration === "1 Month") $start->modify('+1 month');
elseif ($duration === "2 Months") $start->modify('+2 months');
elseif ($duration === "6 Months") $start->modify('+6 months');
elseif ($duration === "15 Days") $start->modify('+15 days');
$end_date = $start->format('Y-m-d');

// 3. Handle Files
$photoResult = handleFileUpload('photo', 'uploads/photos/', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024);
$idProofResult = handleFileUpload('id_proof', 'uploads/documents/', ['pdf'], 5 * 1024 * 1024);

// 4. Update or Insert
if ($id > 0) {
    // UPDATE
    $sql = "UPDATE students SET name=?, father_name=?, mother_name=?, gender=?, dob=?, email=?, phone=?, address=?, city=?, district=?, pincode=?, course=?, duration=?, time_slot=?, joining_date=?, end_date=?, qualification=? ";
    $params = [$name, $father_name, $mother_name, $gender, $dob, $email, $phone, $address, $city, $district, $pincode, $course, $duration, $time_slot, $joining_date, $end_date, $qualification];
    $types = "sssssssssssssssss";

    if ($photoResult['success']) {
        $sql .= ", photo_path=?";
        $params[] = $photoResult['path'];
        $types .= "s";
    }
    if ($idProofResult['success']) {
        $sql .= ", id_proof_path=?";
        $params[] = $idProofResult['path'];
        $types .= "s";
    }

    $sql .= " WHERE id=?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
} else {
    // INSERT (NEW STUDENT)
    // Generate Student ID based on last existing student_id in DB
    // This ensures IDs continue from the actual last record, not from auto-increment gaps
    $res = $conn->query("SELECT student_id FROM students ORDER BY student_id DESC LIMIT 1");
    $lastNum = 0;
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        // Extract numeric part from student_id (e.g., "ICC0005" -> 5)
        $lastNum = (int)preg_replace('/[^0-9]/', '', $row['student_id']);
    }
    $student_id = "ICC" . str_pad($lastNum + 1, 4, "0", STR_PAD_LEFT);

    $photoPath = $photoResult['success'] ? $photoResult['path'] : '';
    $idProofPath = $idProofResult['success'] ? $idProofResult['path'] : '';

    $sql = "INSERT INTO students (student_id, name, father_name, mother_name, gender, dob, email, phone, address, city, district, pincode, course, duration, time_slot, joining_date, end_date, qualification, photo_path, id_proof_path, verification) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Successful')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssssss", 
        $student_id, $name, $father_name, $mother_name, $gender, $dob, $email, $phone, $address, $city, $district, $pincode, $course, $duration, $time_slot, $joining_date, $end_date, $qualification, $photoPath, $idProofPath
    );
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
$stmt->close();
exit;
?>
