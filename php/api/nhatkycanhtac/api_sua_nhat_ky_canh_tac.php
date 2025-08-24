<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';


$in = json_decode(file_get_contents('php://input'), true) ?: [];

$MaNhatKy = $in['MaNhatKy'] ?? '';
$MaVung = $in['MaVung'] ?? '';
$MaHo = $in['MaHo'] ?? '';
$NgayThucHien = $in['NgayThucHien'] ?? '';
$HoatDong = $in['HoatDong'] ?? '';
$GhiChu = $in['GhiChu'] ?? '';

if (!$MaNhatKy || !$MaVung || !$MaHo || !$NgayThucHien || !$HoatDong) {
    echo json_encode(['success' => false, 'error' => 'Thiếu trường bắt buộc']);
    exit;
}

$stmt = $conn->prepare("UPDATE nhatkycanhtac SET MaVung=?, MaHo=?, NgayThucHien=?, HoatDong=?, GhiChu=? WHERE MaNhatKy=?");
$stmt->bind_param("ssssss", $MaVung, $MaHo, $NgayThucHien, $HoatDong, $GhiChu, $MaNhatKy);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật nhật ký']);
} else {
    echo json_encode(['success' => false, 'error' => 'UPDATE_FAILED', 'message' => 'Không thể cập nhật dữ liệu']);
}
