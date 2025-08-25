<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';
$in = json_decode(file_get_contents('php://input'), true) ?? [];
$MaHo = $_GET['MaHo'] ?? $in['MaHo'] ?? '';

if (!$MaHo) {
    echo json_encode(['success' => false, 'error' => 'Thiếu MaHo']);
    exit;
}

// Câu truy vấn gộp 3 bảng
$sql = "
SELECT 
    v.MaVung, v.TenVung, v.DienTich, v.LoaiDat, v.DieuKienTN, v.ViTriToaDo, v.MaGiong, v.TrangThaiVung,
    vt.MaThietBi, vt.SoLuong, vt.TrangThaiSuDung, vt.NgayBatDauSuDung,
    tm.TenThietBi, tm.LoaiThietBi, tm.TrangThai AS TrangThaiMayMoc, tm.NgayNhap, tm.NhaSanXuat
FROM vungtrong v
LEFT JOIN vungtrong_thietbi vt ON v.MaVung = vt.MaVung
LEFT JOIN thietbimaymoc tm ON vt.MaThietBi = tm.MaThietBi
WHERE v.MaHo = ?
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
