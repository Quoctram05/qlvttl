<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';


$in = json_decode(file_get_contents('php://input'), true) ?: [];

$TenDangNhap = trim($in['TenDangNhap'] ?? '');
$MatKhau     = trim($in['MatKhau'] ?? '');
$MaHo        = trim($in['MaHo'] ?? '');
$Email       = trim($in['Email'] ?? '');

if ($TenDangNhap === '' || $MatKhau === '' || $MaHo === '') {
    echo json_encode([
        'success' => false,
        'error' => 'Thiếu trường bắt buộc'
    ]);
    exit;
}

// Kiểm tra tài khoản đã tồn tại
$check = $conn->prepare("SELECT 1 FROM taikhoannguoidung WHERE TenDangNhap = ? OR Email = ? LIMIT 1");
$check->bind_param("ss", $TenDangNhap, $Email);
$check->execute();
$result = $check->get_result();
if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Tên đăng nhập hoặc email đã tồn tại'
    ]);
    exit;
}
$check->close();

// Mã hóa mật khẩu
$MatKhauHashed = password_hash($MatKhau, PASSWORD_DEFAULT);

// Tạo mã tài khoản tự động (ví dụ: TK01, TK02...)
$getLast = $conn->query("SELECT MaTaiKhoan FROM taikhoannguoidung ORDER BY MaTaiKhoan DESC LIMIT 1");
if ($getLast->num_rows > 0) {
    $row = $getLast->fetch_assoc();
    $num = (int) filter_var($row['MaTaiKhoan'], FILTER_SANITIZE_NUMBER_INT);
    $MaTaiKhoan = 'TK' . str_pad($num + 1, 2, '0', STR_PAD_LEFT);
} else {
    $MaTaiKhoan = 'TK01';
}

$VaiTro = 'nong_dan';
$TrangThai = 1;
$NgayDangKy = date('Y-m-d');

$insert = $conn->prepare("INSERT INTO taikhoannguoidung (MaTaiKhoan, TenDangNhap, MatKhau, VaiTro, TrangThai, NgayDangKy, MaHo, Email)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("ssssisss", $MaTaiKhoan, $TenDangNhap, $MatKhauHashed, $VaiTro, $TrangThai, $NgayDangKy, $MaHo, $Email);

if ($insert->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Đăng ký thành công',
        'user' => [
            'MaTaiKhoan' => $MaTaiKhoan,
            'TenDangNhap' => $TenDangNhap,
            'VaiTro' => $VaiTro,
            'MaHo' => $MaHo,
            'Email' => $Email
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'INSERT_FAILED',
        'message' => 'Không thể tạo tài khoản',
        'debug' => $insert->error 
    ]);
}
