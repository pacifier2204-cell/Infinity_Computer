<?php
require_once 'config.php';
// checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
    exit;
}

// Optional: Fetch file paths to delete physical files
/*
$res = $conn->query("SELECT photo_path, id_proof_path FROM students WHERE id = $id");
if ($row = $res->fetch_assoc()) {
    if ($row['photo_path'] && file_exists('../' . $row['photo_path'])) unlink('../' . $row['photo_path']);
    if ($row['id_proof_path'] && file_exists('../' . $row['id_proof_path'])) unlink('../' . $row['id_proof_path']);
}
*/

$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
$stmt->close();
exit;
?>
