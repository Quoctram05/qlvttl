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

$stmt = $conn->prepare("INSERT INTO nhatkycanhtac (MaNhatKy, MaVung, MaHo, NgayThucHien, HoatDong, GhiChu) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $MaNhatKy, $MaVung, $MaHo, $NgayThucHien, $HoatDong, $GhiChu);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã thêm nhật ký']);
} else {
    echo json_encode(['success' => false, 'error' => 'INSERT_FAILED', 'message' => 'Không thể thêm dữ liệu']);
}
