<?php
require_once 'config.php';
checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID.']);
    exit;
}

// Optional: Fetch file paths to delete physical files
$res = $conn->query("SELECT document_path, laptop_images FROM second_hand_laptop_requests WHERE id = $id");
if ($row = $res->fetch_assoc()) {
    if ($row['document_path'] && file_exists('../' . $row['document_path'])) unlink('../' . $row['document_path']);
    
    $images = json_decode($row['laptop_images'], true);
    if ($images && is_array($images)) {
        foreach($images as $img) {
            if ($img && file_exists('../' . $img)) unlink('../' . $img);
        }
    }
}

$stmt = $conn->prepare("DELETE FROM second_hand_laptop_requests WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
$stmt->close();
exit;
?>
