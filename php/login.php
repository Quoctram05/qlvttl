<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/connect.php';
session_start();
if ($conn->connect_error) {
    echo json_encode(["error" => "❌ Kết nối thất bại: " . $conn->connect_error]);
    exit;
}
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$u = trim($in['TenDangNhap'] ?? ''); $p = (string)($in['MatKhau'] ?? '');
if ($u===''||$p===''){ echo json_encode(['success'=>false,'error'=>'Thiếu TenDangNhap/MatKhau']); exit; }

$q = $conn->prepare("SELECT * FROM taikhoannguoidung WHERE TenDangNhap=? LIMIT 1");
$q->bind_param("s",$u); $q->execute(); $user=$q->get_result()->fetch_assoc(); $q->close();
if(!$user){ echo json_encode(['success'=>false,'error'=>'Sai tài khoản hoặc mật khẩu']); exit; }

$ok = password_verify($p,$user['MatKhau']);
if(!$ok && hash_equals($user['MatKhau'],$p)){ // nâng cấp plaintext -> hash
  $new = password_hash($p,PASSWORD_DEFAULT);
  $up = $conn->prepare("UPDATE taikhoannguoidung SET MatKhau=? WHERE TenDangNhap=?");
  $up->bind_param("ss",$new,$u); $up->execute(); $up->close();
  $ok = true;
}
// Nếu vẫn không đúng
if (!$ok) {
    echo json_encode([
        'success' => false,
        'error' => 'Sai tài khoản hoặc mật khẩu'
    ]);
    exit;
}

// Nếu bị khóa
if ((int)$user['TrangThai'] !== 1) {
    echo json_encode([
        'success' => false,
        'error' => 'Tài khoản đã bị khóa'
    ]);
    exit;
}

// Đăng nhập thành công → tạo session + trả token và user
$_SESSION['user'] = [
    'MaTaiKhoan'   => $user['MaTaiKhoan'],
    'TenDangNhap'  => $user['TenDangNhap'],
    'VaiTro'       => $user['VaiTro'],
    'TrangThai'    => $user['TrangThai']
];

echo json_encode([
    'success' => true,
    'message' => 'Đăng nhập thành công!',
    'token'   => session_id(),
    'user'    => $_SESSION['user']
], JSON_UNESCAPED_UNICODE);
