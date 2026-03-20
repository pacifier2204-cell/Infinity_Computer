<?php
require_once 'config/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Robust File Upload Handler
 */
function handleFileUpload($fileKey, $targetDir, $allowedTypes, $maxSize) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => "Upload failed for $fileKey. Error Code: " . ($_FILES[$fileKey]['error'] ?? 'MISSING')];
    }

    $file = $_FILES[$fileKey];
    $originalName = basename($file['name']);
    $fileSize = $file['size'];
    $tmpName = $file['tmp_name'];
    
    // 1. Extension Validation
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => "Invalid file type for $fileKey. Allowed: " . implode(', ', $allowedTypes)];
    }

    // 2. Size Validation
    if ($fileSize > $maxSize) {
        return ['success' => false, 'error' => "File $fileKey exceeds allowed size."];
    }

    // 3. MIME Type Validation
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);
    $validMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf'
    ];
    
    if (!isset($validMimes[$ext]) || $validMimes[$ext] !== $mime) {
        return ['success' => false, 'error' => "MIME type mismatch for $fileKey."];
    }

    // 4. Directory Check & Creation
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 5. Secure Renaming
    $newName = time() . '_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);
    $destPath = $targetDir . $newName;

    // 6. Move File
    if (move_uploaded_file($tmpName, $destPath)) {
        return ['success' => true, 'path' => $destPath];
    }

    return ['success' => false, 'error' => "Failed to move uploaded file $fileKey."];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $fatherName = isset($_POST['fatherName']) ? trim($_POST['fatherName']) : '';
    $motherName = isset($_POST['motherName']) ? trim($_POST['motherName']) : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    $qualification = isset($_POST['qualification']) ? trim($_POST['qualification']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $district = isset($_POST['district']) ? trim($_POST['district']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
    $course = isset($_POST['course']) ? $_POST['course'] : '';
    $timeSlot = isset($_POST['timeSlot']) ? $_POST['timeSlot'] : '';
    $joiningDate = isset($_POST['joiningDate']) ? $_POST['joiningDate'] : date('Y-m-d');

    // Basic validation
    $requiredFields = [
        'fullName' => 'Full Name',
        'fatherName' => "Father's Name",
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'dob' => 'Date of Birth',
        'address' => 'Residential Address',
        'course' => 'Course Selection',
        'city' => 'City',
        'district' => 'District',
        'pincode' => 'Pincode'
    ];

    $errors = [];
    foreach ($requiredFields as $field => $label) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = $label;
        }
    }

    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        $errors[] = 'reCAPTCHA Verification';
    }

    if (!empty($errors)) {
        die("Error: Please fill all required fields: " . implode(', ', $errors));
    }

    $recaptchaSecret = '6LcadY0sAAAAAE-ADcAzbPWGpJLAdi1oW2jLB4Qe';
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $responseData = json_decode($verifyResponse);

    if (!$responseData->success) {
        die("Error: Robot verification failed. Please try again.");
    }

    // File Uploads
    $photoResult = handleFileUpload('photo', 'uploads/photos/', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024);
    $idProofResult = handleFileUpload('id_proof', 'uploads/documents/', ['pdf'], 5 * 1024 * 1024);

    if (!$photoResult['success']) die("Photo Error: " . $photoResult['error']);
    if (!$idProofResult['success']) die("ID Proof Error: " . $idProofResult['error']);

    $photoPath = $photoResult['path'];
    $idProofPath = $idProofResult['path'];

    // Generate Unique Student ID based on last existing student_id in DB
    // This ensures IDs continue from the actual last record, not from auto-increment gaps
    $result = $conn->query("SELECT student_id FROM students ORDER BY student_id DESC LIMIT 1");
    $lastNum = 0;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Extract numeric part from student_id (e.g., "ICC0005" -> 5)
        $lastNum = (int)preg_replace('/[^0-9]/', '', $row['student_id']);
    }
    $nextNum = $lastNum + 1;
    $studentId = "ICC" . str_pad($nextNum, 4, "0", STR_PAD_LEFT);

    // Duration mapping
    $durations = [
        "TALLY" => "1.5 Months", "DTP" => "1.5 Months", "BASIC" => "1.5 Months",
        "Web Designing" => "1.5 Months", "C Programming" => "1.5 Months",
        "C++ Programming" => "1.5 Months", "Hardware Networking" => "6 Months",
        "Python Level 1" => "1.5 Months", "ADVANCE TALLY" => "1 Month",
        "ADVANCE Excel" => "15 Days", "Advance Word" => "15 Days", "ADVANCE BASIC" => "2 Months"
    ];
    $duration = $durations[$course] ?? "Varies";

    // End date calculation
    $start = new DateTime($joiningDate);
    if ($duration === "1.5 Months") $start->modify('+45 days');
    elseif ($duration === "1 Month") $start->modify('+1 month');
    elseif ($duration === "2 Months") $start->modify('+2 months');
    elseif ($duration === "6 Months") $start->modify('+6 months');
    elseif ($duration === "15 Days") $start->modify('+15 days');
    $endDate = $start->format('Y-m-d');

    // Insert into database
    $sql = "INSERT INTO students (student_id, name, father_name, mother_name, gender, email, phone, address, city, district, pincode, dob, qualification, joining_date, end_date, course, duration, time_slot, photo_path, id_proof_path, verification) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssssss", 
        $studentId, $fullName, $fatherName, $motherName, $gender, $email, $phone, $address, $city, $district, $pincode, $dob, $qualification, 
        $joiningDate, $endDate, $course, $duration, $timeSlot, $photoPath, $idProofPath
    );

    if ($stmt->execute()) {
        header("Location: enrollment_confirmation.php?sid=$studentId");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
