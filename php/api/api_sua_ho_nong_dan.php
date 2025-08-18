<?php
include "connect.php";


if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["success"=>false,"error"=>"METHOD_NOT_ALLOWED","message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Lấy MaHo từ query hoặc body */
$MaHo = isset($_GET['MaHo']) ? trim($_GET['MaHo']) : null;

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
if (!is_array($body)) $body = [];

/* Nếu body có MaHo thì ưu tiên query trước, body sau */
if (!$MaHo && isset($body['MaHo'])) $MaHo = trim($body['MaHo']);

if (!$MaHo) {
    http_response_code(400);
    echo json_encode(["success"=>false,"error"=>"MISSING_MAHO","message"=>"Thiếu MaHo (query ?MaHo= hoặc trong body)"], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Kiểm tra tồn tại hộ */
$chk = $conn->prepare("SELECT MaHo FROM honongdan WHERE MaHo=? LIMIT 1");
$chk->bind_param("s", $MaHo);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    http_response_code(404);
    echo json_encode(["success"=>false,"error"=>"NOT_FOUND","message"=>"Không tìm thấy hộ nông dân với MaHo = $MaHo"], JSON_UNESCAPED_UNICODE);
    exit;
}
$chk->close;

/* Lấy các trường cho phép cập nhật */
$fields = [];
$params = [];
$types  = "";

/* TenChuHo */
if (isset($body['TenChuHo']) && $body['TenChuHo'] !== "") {
    $fields[] = "TenChuHo = ?";
    $params[] = trim($body['TenChuHo']);
    $types   .= "s";
}

/* CCCD (12 số) */
if (isset($body['CCCD']) && $body['CCCD'] !== "") {
    $CCCD = trim($body['CCCD']);
    if (!preg_match('/^[0-9]{12}$/', $CCCD)) {
        http_response_code(400);
        echo json_encode(["success"=>false,"error"=>"INVALID_CCCD","message"=>"CCCD phải gồm đúng 12 chữ số"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // kiểm tra trùng CCCD của hộ khác
    $dup = $conn->prepare("SELECT 1 FROM honongdan WHERE CCCD=? AND MaHo<>? LIMIT 1");
    $dup->bind_param("ss", $CCCD, $MaHo);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) {
        $dup->close();
        http_response_code(409);
        echo json_encode(["success"=>false,"error"=>"DUPLICATE_CCCD","message"=>"CCCD đã tồn tại ở hộ khác"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $dup->close();

    $fields[] = "CCCD = ?";
    $params[] = $CCCD;
    $types   .= "s";
}

/* GioiTinh (0|1) */
if (array_key_exists('GioiTinh', $body)) {
    $gt = (int)$body['GioiTinh'];
    if (!in_array($gt, [0,1], true)) {
        http_response_code(400);
        echo json_encode(["success"=>false,"error"=>"INVALID_GIOITINH","message"=>"GioiTinh phải là 0 (Nam) hoặc 1 (Nữ)"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $fields[] = "GioiTinh = ?";
    $params[] = $gt;
    $types   .= "i";
}

/* NgaySinh (YYYY-MM-DD hoặc DD/MM/YYYY) */
if (isset($body['NgaySinh']) && $body['NgaySinh'] !== "") {
    $ns = str_replace('/', '-', trim($body['NgaySinh']));
    $ts = strtotime($ns);
    if ($ts === false) {
        http_response_code(400);
        echo json_encode(["success"=>false,"error"=>"INVALID_DATE","message"=>"NgàySinh không hợp lệ"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $ns = date('Y-m-d', $ts);
    $fields[] = "NgaySinh = ?";
    $params[] = $ns;
    $types   .= "s";
}

/* SDT */
if (isset($body['SDT']) && $body['SDT'] !== "") {
    $fields[] = "SDT = ?";
    $params[] = trim($body['SDT']);
    $types   .= "s";
}

/* DiaChi */
if (isset($body['DiaChi']) && $body['DiaChi'] !== "") {
    $fields[] = "DiaChi = ?";
    $params[] = trim($body['DiaChi']);
    $types   .= "s";
}

/* Không có trường nào để cập nhật */
if (empty($fields)) {
    http_response_code(400);
    echo json_encode(["success"=>false,"error"=>"NO_FIELDS","message"=>"Không có trường dữ liệu nào để cập nhật"], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Tạo câu SQL động */
$sql = "UPDATE honongdan SET " . implode(", ", $fields) . " WHERE MaHo = ?";
$params[] = $MaHo;
$types   .= "s";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"PREPARE_FAILED","debug"=>$conn->error], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Gắn tham số động */
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Cập nhật thông tin hộ nông dân thành công",
        "updated" => $stmt->affected_rows
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "UPDATE_FAILED",
        "message" => "Không thể cập nhật",
        "debug"   => $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
