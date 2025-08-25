<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../../connect.php';

$MaHo = $_GET['MaHo'] ?? '';
if (!$MaHo) {
    echo json_encode(['success' => false, 'error' => 'Thiếu mã hộ']);
    exit;
}

$sql = "
SELECT DISTINCT 
    gtl.MaGiong,
    gtl.TenGiong,
    gtl.NguonGoc,
    gtl.DacDiem,
    gtl.NgayApDung
FROM vungtrong vt
JOIN giongthanhlong gtl ON vt.MaGiong = gtl.MaGiong
WHERE vt.MaHo = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $MaHo);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $data
], JSON_UNESCAPED_UNICODE);
