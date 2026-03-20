<?php
require_once 'config.php';
checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

/**
 * Handle File Upload
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
        return ['success' => false, 'error' => "skip"];
    }

    $originalName = basename($fileData['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedTypes)) return ['success' => false, 'error' => "Invalid type"];
    if ($fileData['size'] > $maxSize) return ['success' => false, 'error' => "File too large"];

    if (!is_dir('../' . $targetDir)) mkdir('../' . $targetDir, 0755, true);

    $newName = time() . '_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);
    $destPath = $targetDir . $newName;

    if (move_uploaded_file($fileData['tmp_name'], '../' . $destPath)) {
        return ['success' => true, 'path' => $destPath];
    }
    return ['success' => false, 'error' => "Move failed"];
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$owner_name = sanitizeInput($_POST['owner_name']);
$email = sanitizeInput($_POST['email']);
$mobile = sanitizeInput($_POST['mobile']);
$address = sanitizeInput($_POST['address']);
$laptop_company = sanitizeInput($_POST['laptop_company']);
$laptop_model = sanitizeInput($_POST['laptop_model']);
$serial_number = sanitizeInput($_POST['serial_number']);
$expected_price = (float)$_POST['expected_price'];
$description = sanitizeInput($_POST['description']);

// Handle Docs
$docResult = handleFileUpload('document_path', 'uploads/laptops/docs/', ['jpg', 'jpeg', 'png', 'pdf'], 5 * 1024 * 1024);

// Handle Photos
$newPhotosData = [];
$photosResultSuccess = false;
if (isset($_FILES['laptop_images']) && !empty($_FILES['laptop_images']['name'][0])) {
    $count = count($_FILES['laptop_images']['name']);
    for ($i = 0; $i < $count; $i++) {
        $imgRes = handleFileUpload('laptop_images', 'uploads/laptops/photos/', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024, true, $i);
        if ($imgRes['success']) {
            $newPhotosData[] = $imgRes['path'];
            $photosResultSuccess = true;
        }
    }
}

$sql = "UPDATE second_hand_laptop_requests SET owner_name=?, email=?, mobile=?, address=?, laptop_company=?, laptop_model=?, serial_number=?, expected_price=?, description=?";
$params = [$owner_name, $email, $mobile, $address, $laptop_company, $laptop_model, $serial_number, $expected_price, $description];
$types = "ssssssdss"; // Uses 'd' for double/float expected_price

if ($docResult['success']) {
    $sql .= ", document_path=?";
    $params[] = $docResult['path'];
    $types .= "s";
}

if ($photosResultSuccess && count($newPhotosData) > 0) {
    $sql .= ", laptop_images=?";
    $params[] = json_encode($newPhotosData);
    $types .= "s";
}

$sql .= " WHERE id=?";
$params[] = $id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
$stmt->close();
exit;
?>
