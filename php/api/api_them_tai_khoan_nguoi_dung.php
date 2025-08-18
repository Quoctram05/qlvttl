<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!is_array($b) || empty($b)) $b = $_POST;

$MaTaiKhoan  = isset($b['MaTaiKhoan'])  ? trim($b['MaTaiKhoan'])  : null;
$TenDangNhap = isset($b['TenDangNhap']) ? trim($b['TenDangNhap']) : null;
$MatKhau     = isset($b['MatKhau'])     ? trim($b['MatKhau'])     : null;
$VaiTro      = isset($b['VaiTro'])      ? trim($b['VaiTro'])      : null; // 'admin' / 'user'
$TrangThai   = isset($b['TrangThai'])   ? (int)$b['TrangThai']    : 1;  // 1: active, 0: inactive
$MaHo        = isset($b['MaHo'])        ? trim($b['MaHo'])        : null;

$missing=[]; foreach(['MaTaiKhoan','TenDangNhap','MatKhau','VaiTro'] as $k) if (empty($$k)) $missing[]=$k;
if ($missing) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }

if (!preg_match('/^[A-Za-z0-9]{1,5}$/', $MaTaiKhoan)) { echo json_encode(["success"=>false,"error"=>"INVALID_MATAIKHOAN"], JSON_UNESCAPED_UNICODE); exit; }
if (strlen($MatKhau) < 6) { echo json_encode(["success"=>false,"error"=>"INVALID_PASSWORD"], JSON_UNESCAPED_UNICODE); exit; }
if (!in_array($VaiTro, ['admin', 'user'], true)) { echo json_encode(["success"=>false,"error"=>"INVALID_VAITRO"], JSON_UNESCAPED_UNICODE); exit; }

/* Kiểm tra tài khoản đã tồn tại */
$st=$conn->prepare("SELECT 1 FROM taikhoannguoidung WHERE TenDangNhap=? LIMIT 1");
$st->bind_param("s", $TenDangNhap); $st->execute(); $st->store_result();
if ($st->num_rows > 0) {
    $st->close();
    http_response_code(409);
    echo json_encode(["success"=>false,"error"=>"DUPLICATE_TENDANGNHAP"], JSON_UNESCAPED_UNICODE);
    exit;
}
$st->close();

/* Kiểm tra MaHo tồn tại */
$st=$conn->prepare("SELECT 1 FROM honongdan WHERE MaHo=? LIMIT 1");
$st->bind_param("s", $MaHo); $st->execute(); $st->store_result();
if ($st->num_rows === 0) {
    $st->close();
    http_response_code(400);
    echo json_encode(["success"=>false,"error"=>"INVALID_MAHO"], JSON_UNESCAPED_UNICODE);
    exit;
}
$st->close();

/* Mã hóa mật khẩu */
$hashedPassword = password_hash($MatKhau, PASSWORD_BCRYPT);

/* Thêm tài khoản */
$sql = "INSERT INTO taikhoannguoidung (MaTaiKhoan, TenDangNhap, MatKhau, VaiTro, TrangThai, NgayDangKy, MaHo)
        VALUES (?,?,?,?,?,NOW(),?)";
$st = $conn->prepare($sql);
$st->bind_param("sssiss", $MaTaiKhoan, $TenDangNhap, $hashedPassword, $VaiTro, $TrangThai, $MaHo);

if ($st->execute()) {
    echo json_encode(["success"=>true,"message"=>"Tạo tài khoản thành công", "data"=>compact('MaTaiKhoan', 'TenDangNhap', 'VaiTro', 'TrangThai', 'MaHo')], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
