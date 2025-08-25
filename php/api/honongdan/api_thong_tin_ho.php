<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';

// Nhận MaHo từ request
$MaHo = $_GET['MaHo'] ?? $_POST['MaHo'] ?? '';

if (empty($MaHo)) {
    echo json_encode([
        'success' => false,
        'error' => 'Thiếu mã hộ cần truy vấn'
    ]);
    exit;
}

// Truy vấn thông tin hộ nông dân theo MaHo
$stmt = $conn->prepare("SELECT * FROM honongdan WHERE MaHo = ?");
$stmt->bind_param("s", $MaHo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => true,
        'data' => $result->fetch_assoc()
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Không tìm thấy thông tin hộ nông dân'
    ]);
}
