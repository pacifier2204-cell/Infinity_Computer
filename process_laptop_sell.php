<?php
require_once 'config/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

/**
 * Enhanced File Upload Handler
 */
function handleFileUpload($fileKey, $targetDir, $allowedTypes, $maxSize, $isMultiple = false, $index = null) {
    $fileData = $isMultiple ? [
        'name' => $_FILES[$fileKey]['name'][$index],
        'type' => $_FILES[$fileKey]['type'][$index],
        'tmp_name' => $_FILES[$fileKey]['tmp_name'][$index],
        'error' => $_FILES[$fileKey]['error'][$index],
        'size' => $_FILES[$fileKey]['size'][$index]
    ] : $_FILES[$fileKey];

    if (!isset($fileData) || $fileData['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => "Upload failed. Error Code: " . ($fileData['error'] ?? 'MISSING')];
    }

    $originalName = basename($fileData['name']);
    $fileSize = $fileData['size'];
    $tmpName = $fileData['tmp_name'];
    
    // 1. Extension Validation
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => "Invalid file type. Allowed: " . implode(', ', $allowedTypes)];
    }

    // 2. Size Validation
    if ($fileSize > $maxSize) {
        return ['success' => false, 'error' => "File exceeds allowed size (" . ($maxSize / 1024 / 1024) . "MB)."];
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
        return ['success' => false, 'error' => "MIME type mismatch ($mime for $ext)."];
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

    return ['success' => false, 'error' => "Failed to move uploaded file."];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization
    $ownerName = isset($_POST['ownerName']) ? strip_tags(trim($_POST['ownerName'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $mobile = isset($_POST['mobile']) ? preg_replace('/[^0-9]/', '', $_POST['mobile']) : '';
    $address = isset($_POST['address']) ? strip_tags(trim($_POST['address'])) : '';
    
    $gadgetType = isset($_POST['gadgetType']) ? strip_tags(trim($_POST['gadgetType'])) : '';
    $laptopCompany = isset($_POST['laptopCompany']) ? strip_tags(trim($_POST['laptopCompany'])) : '';
    $laptopModel = isset($_POST['laptopModel']) ? strip_tags(trim($_POST['laptopModel'])) : '';
    $serialNumber = isset($_POST['serialNumber']) ? strip_tags(trim($_POST['serialNumber'])) : '';
    $expectedPrice = isset($_POST['expectedPrice']) ? (float)$_POST['expectedPrice'] : 0;
    $description = isset($_POST['description']) ? strip_tags(trim($_POST['description'])) : '';
    
    $legalDeclaration = isset($_POST['legalDeclaration']) ? true : false;
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

    // Validation
    $errors = [];
    if (empty($ownerName)) $errors[] = "Owner Name";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email";
    if (strlen($mobile) != 10) $errors[] = "10-digit Mobile Number";
    if (empty($address)) $errors[] = "Address";
    if (empty($gadgetType)) $errors[] = "Gadget Type";
    if (empty($laptopCompany)) $errors[] = "Brand / Company";
    if (empty($laptopModel)) $errors[] = "Model Name/Number";
    if (empty($serialNumber)) $errors[] = "Serial Number";
    if ($expectedPrice <= 0) $errors[] = "Expected Selling Price";
    if (!$legalDeclaration) $errors[] = "Legal Declaration Checkbox";
    if (empty($recaptchaResponse)) $errors[] = "reCAPTCHA Verification";

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => "Please fill all required fields correctly: " . implode(', ', $errors)]);
        exit;
    }

    // reCAPTCHA Server-side Verification
    $recaptchaSecret = '6LcadY0sAAAAAE-ADcAzbPWGpJLAdi1oW2jLB4Qe';
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $responseData = json_decode($verifyResponse);

    if (!$responseData || !$responseData->success) {
        echo json_encode(['success' => false, 'message' => "Robot verification failed. Please try again."]);
        exit;
    }

    // SECTION C: Document Upload (Mandatory)
    $docResult = handleFileUpload('addressProof', 'uploads/laptops/docs/', ['jpg', 'jpeg', 'png', 'pdf'], 5 * 1024 * 1024);
    if (!$docResult['success']) {
        echo json_encode(['success' => false, 'message' => "Document Error: " . $docResult['error']]);
        exit;
    }
    $documentPath = $docResult['path'];

    // SECTION D: Laptop Photos (Optional)
    $laptopImages = [];
    if (isset($_FILES['laptopPhotos']) && !empty($_FILES['laptopPhotos']['name'][0])) {
        $count = count($_FILES['laptopPhotos']['name']);
        for ($i = 0; $i < $count; $i++) {
            $imgResult = handleFileUpload('laptopPhotos', 'uploads/laptops/photos/', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024, true, $i);
            if ($imgResult['success']) {
                $laptopImages[] = $imgResult['path'];
            }
        }
    }
    $laptopImagesJson = json_encode($laptopImages);

    // Insert into Database
    $sql = "INSERT INTO second_hand_laptop_requests 
            (owner_name, email, mobile, address, gadget_type, laptop_company, laptop_model, serial_number, expected_price, description, document_path, laptop_images, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssdsss", 
        $ownerName, $email, $mobile, $address, $gadgetType, $laptopCompany, $laptopModel, $serialNumber, $expectedPrice, $description, $documentPath, $laptopImagesJson
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Your request has been submitted successfully. You will be notified about your request through email soon."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => "Database error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
