<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../connect.php';

$MaHo = $_GET['MaHo'] ?? '';
if ($MaHo === '') {
    echo json_encode(['success' => false, 'error' => 'Thiếu MaHo']);
    exit;
}

$sql = "
SELECT
    nkct.MaNhatKy,
    nkct.MaVung,
    nkct.NgayThucHien,
    nkct.HoatDong,
    nkct.GhiChu,
    
    vt.MaVatTu,
    vt.TenVatTu,
    vt.LoaiVatTu,
    vt.DonViTinh,
    vt.NgayNhap,

    nccvt.GiaBan,
    nccvt.SoLuong AS SL_NCC,
    nccvt.ThoiGianCungCap,

    ncc.MaNCC,
    ncc.TenNCC,
    ncc.DiaChi,
    ncc.SDT,
    ncc.Email,

    nkvt.SoLuong AS SL_SuDung
FROM nhatkycanhtac nkct
JOIN nhatky_vattu nkvt ON nkct.MaNhatKy = nkvt.MaNhatKy
JOIN vattunongnghiep vt ON nkvt.MaVatTu = vt.MaVatTu
LEFT JOIN nhacungcap_vattu nccvt ON vt.MaVatTu = nccvt.MaVatTu
LEFT JOIN nhacungcapvattu ncc ON ncc.MaNCC = nccvt.MaNCC
WHERE nkct.MaHo = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $MaHo);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
