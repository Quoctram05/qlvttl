<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error"   => "METHOD_NOT_ALLOWED",
        "message" => "Chỉ hỗ trợ phương thức DELETE"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Lấy MaHo từ query hoặc từ body JSON */
$MaHo = isset($_GET['MaHo']) ? trim($_GET['MaHo']) : null;

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$MaHo && is_array($data) && isset($data['MaHo'])) {
    $MaHo = trim($data['MaHo']);
}

if (!$MaHo) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error"   => "MISSING_MAHO",
        "message" => "Thiếu MaHo (truyền ?MaHo= hoặc trong body)"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Kiểm tra tồn tại hộ */
$stmt = $conn->prepare("SELECT MaHo FROM honongdan WHERE MaHo=? LIMIT 1");
$stmt->bind_param("s", $MaHo);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "error"   => "NOT_FOUND",
        "message" => "Không tìm thấy hộ nông dân có MaHo = $MaHo"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

/* Thực hiện xóa */
$stmt = $conn->prepare("DELETE FROM honongdan WHERE MaHo=?");
$stmt->bind_param("s", $MaHo);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Đã xóa hộ nông dân thành công",
        "deleted" => $MaHo
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "DELETE_FAILED",
        "message" => "Không thể xóa dữ liệu",
        "debug"   => $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
