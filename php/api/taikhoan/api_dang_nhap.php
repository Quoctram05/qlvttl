<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';

$in = json_decode(file_get_contents('php://input'), true) ?: [];

$TenDangNhap = trim($in['TenDangNhap'] ?? '');
$MatKhau     = (string)($in['MatKhau'] ?? '');

if ($TenDangNhap === '' || $MatKhau === '') {
    echo json_encode(['success' => false, 'error' => 'Thiếu tên đăng nhập hoặc mật khẩu']); exit;
}

$query = $conn->prepare("SELECT * FROM taikhoannguoidung WHERE TenDangNhap=? AND TrangThai=1 LIMIT 1");
$query->bind_param("s", $TenDangNhap);
$query->execute();
$result = $query->get_result();

if (!$result->num_rows) {
    echo json_encode(['success' => false, 'error' => 'Tài khoản không tồn tại hoặc bị khóa']); exit;
}

$row = $result->fetch_assoc();

if (!password_verify($MatKhau, $row['MatKhau'])) {
    echo json_encode(['success' => false, 'error' => 'Sai mật khẩu']); exit;
}

// Xoá mật khẩu khỏi kết quả trả về
unset($row['MatKhau']);

echo json_encode([
    'success' => true,
    'message' => '✅ Đăng nhập thành công',
    'user' => $row,
    'token' => base64_encode($row['TenDangNhap'] . '|' . time()) // hoặc JWT nếu bạn triển khai sau
], JSON_UNESCAPED_UNICODE);
