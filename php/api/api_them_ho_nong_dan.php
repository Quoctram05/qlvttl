<?php
// Kết nối CSDL (đã set UTF-8 và header JSON trong config.php)
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error"   => "METHOD_NOT_ALLOWED",
        "message" => "Chỉ hỗ trợ phương thức POST"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Đọc body JSON; nếu không có JSON thì fallback sang form-data/x-www-form-urlencoded */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data) || empty($data)) {
    // fallback khi gửi form-data
    $data = $_POST;
}

/** Lấy dữ liệu và trim */
$MaHo      = isset($data['MaHo'])      ? trim($data['MaHo'])      : null;
$TenChuHo  = isset($data['TenChuHo'])  ? trim($data['TenChuHo'])  : null;
$CCCD      = isset($data['CCCD'])      ? trim($data['CCCD'])      : null;
$GioiTinh  = isset($data['GioiTinh'])  ? $data['GioiTinh']        : null; // 0 hoặc 1
$NgaySinh  = isset($data['NgaySinh'])  ? trim($data['NgaySinh'])  : null; // YYYY-MM-DD
$SDT       = isset($data['SDT'])       ? trim($data['SDT'])       : null;
$DiaChi    = isset($data['DiaChi'])    ? trim($data['DiaChi'])    : null;

/** Validate bắt buộc */
$required = ['MaHo','TenChuHo','CCCD','GioiTinh','NgaySinh','SDT','DiaChi'];
$missing  = [];
foreach ($required as $k) {
    if (!isset($data[$k]) || $data[$k] === '' || $data[$k] === null) $missing[] = $k;
}
if ($missing) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error"   => "MISSING_FIELDS",
        "message" => "Thiếu trường bắt buộc",
        "debug"   => ["missing" => $missing]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Chuẩn hoá Ngày sinh: chấp nhận 'YYYY-MM-DD' hoặc 'DD/MM/YYYY' */
$NgaySinh = str_replace('/', '-', $NgaySinh);
$ts = strtotime($NgaySinh);
if ($ts === false) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error"   => "INVALID_DATE",
        "message" => "NgàySinh không hợp lệ. Định dạng hợp lệ: YYYY-MM-DD hoặc DD/MM/YYYY"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$NgaySinh = date('Y-m-d', $ts);

/** Ràng buộc đơn giản */
if (!preg_match('/^[A-Za-z0-9]{1,5}$/', $MaHo)) {
    echo json_encode([
        "success" => false,
        "error"   => "INVALID_MAHO",
        "message" => "MaHo chỉ gồm chữ/số, tối đa 5 ký tự"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!preg_match('/^[0-9]{12}$/', $CCCD)) {
    echo json_encode([
        "success" => false,
        "error"   => "INVALID_CCCD",
        "message" => "CCCD phải gồm đúng 12 chữ số"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!in_array((int)$GioiTinh, [0,1], true)) {
    echo json_encode([
        "success" => false,
        "error"   => "INVALID_GIOITINH",
        "message" => "GioiTinh phải là 0 (Nam) hoặc 1 (Nữ)"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Kiểm tra trùng MaHo */
$stmt = $conn->prepare("SELECT MaHo FROM honongdan WHERE MaHo = ? LIMIT 1");
$stmt->bind_param("s", $MaHo);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "error"   => "DUPLICATE_MAHO",
        "message" => "MaHo đã tồn tại"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

/** Kiểm tra trùng CCCD */
$stmt = $conn->prepare("SELECT CCCD FROM honongdan WHERE CCCD = ? LIMIT 1");
$stmt->bind_param("s", $CCCD);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "error"   => "DUPLICATE_CCCD",
        "message" => "CCCD đã tồn tại"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

/** Thêm mới */
$sql = "INSERT INTO honongdan (MaHo, TenChuHo, CCCD, GioiTinh, NgaySinh, SDT, DiaChi)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "PREPARE_FAILED",
        "message" => "Không chuẩn bị được câu lệnh SQL",
        "debug"   => $conn->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$gioitinhInt = (int)$GioiTinh;
$stmt->bind_param("sssisss", $MaHo, $TenChuHo, $CCCD, $gioitinhInt, $NgaySinh, $SDT, $DiaChi);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Thêm hộ nông dân thành công",
        "data" => [
            "MaHo"     => $MaHo,
            "TenChuHo" => $TenChuHo,
            "CCCD"     => $CCCD,
            "GioiTinh" => $gioitinhInt,
            "NgaySinh" => $NgaySinh,
            "SDT"      => $SDT,
            "DiaChi"   => $DiaChi
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "INSERT_FAILED",
        "message" => "Không thể thêm dữ liệu",
        "debug"   => $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}
$stmt->close();
$conn->close();
