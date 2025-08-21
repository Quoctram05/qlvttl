<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaTaiKhoan = isset($_GET['MaTaiKhoan']) ? trim($_GET['MaTaiKhoan']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!is_array($b)) $b = [];
if (!$MaTaiKhoan && isset($b['MaTaiKhoan'])) $MaTaiKhoan = trim($b['MaTaiKhoan']);

if (!$MaTaiKhoan) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MATAIKHOAN"], JSON_UNESCAPED_UNICODE); exit; }

/* Kiểm tra tài khoản đã tồn tại */
$chk = $conn->prepare("SELECT 1 FROM taikhoannguoidung WHERE MaTaiKhoan=? LIMIT 1");
$chk->bind_param("s", $MaTaiKhoan); $chk->execute(); $chk->store_result();
if ($chk->num_rows === 0) { $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

$fields = [];
$types = "";
$params = [];

if (isset($b['TenDangNhap']) && $b['TenDangNhap'] !== "") {
    $fields[] = "TenDangNhap=?";
    $types .= "s";
    $params[] = trim($b['TenDangNhap']);
}

if (isset($b['MatKhau']) && $b['MatKhau'] !== "") {
    if (strlen($b['MatKhau']) < 6) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_PASSWORD"], JSON_UNESCAPED_UNICODE); exit; }
    $hashedPassword = password_hash(trim($b['MatKhau']), PASSWORD_BCRYPT);
    $fields[] = "MatKhau=?";
    $types .= "s";
    $params[] = $hashedPassword;
}

if (isset($b['VaiTro']) && in_array($b['VaiTro'], ['admin', 'user'], true)) {
    $fields[] = "VaiTro=?";
    $types .= "s";
    $params[] = trim($b['VaiTro']);
}

if (isset($b['TrangThai'])) {
    $fields[] = "TrangThai=?";
    $types .= "i";
    $params[] = (int)$b['TrangThai'];
}

if (isset($b['MaHo'])) {
    $fields[] = "MaHo=?";
    $types .= "s";
    $params[] = trim($b['MaHo']);
}

if (empty($fields)) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS"], JSON_UNESCAPED_UNICODE); exit; }

$sql = "UPDATE taikhoannguoidung SET " . implode(", ", $fields) . " WHERE MaTaiKhoan=?";
$types .= "s";
$params[] = $MaTaiKhoan;

$st = $conn->prepare($sql);
$st->bind_param($types, ...$params);

if ($st->execute()) {
    echo json_encode(["success"=>true,"message"=>"Cập nhật tài khoản thành công", "updated"=>$st->affected_rows], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}

$st->close(); $conn->close();
