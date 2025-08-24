<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';


$MaNhatKy = $_GET['MaNhatKy'] ?? '';
if (!$MaNhatKy) {
    echo json_encode(['success' => false, 'error' => 'Thiếu mã nhật ký']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM nhatkycanhtac WHERE MaNhatKy = ?");
$stmt->bind_param("s", $MaNhatKy);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã xoá nhật ký']);
} else {
    echo json_encode(['success' => false, 'error' => 'DELETE_FAILED', 'message' => 'Không thể xoá dữ liệu']);
}
