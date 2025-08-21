<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE); exit;
}

$MaIoT  = isset($_GET['MaIoT'])  ? trim($_GET['MaIoT'])  : null;
$MaVung = isset($_GET['MaVung']) ? trim($_GET['MaVung']) : null;

if ($MaIoT || $MaVung) {
  $sql = "SELECT i.MaIoT, i.LoaiCamBien, i.GiaTriDo, i.DonVi, i.ThoiGianDo, i.TrangThai,
                 i.MaVung, i.CanhBaoNguyen, v.TenVung
          FROM thietbiIoT i
          JOIN vungtrong v ON i.MaVung = v.MaVung
          WHERE 1=1";
  $types = ""; $params = [];
  if ($MaIoT)  { $sql .= " AND i.MaIoT=?";  $types .= "s"; $params[] = $MaIoT; }
  if ($MaVung) { $sql .= " AND i.MaVung=?"; $types .= "s"; $params[] = $MaVung; }

  $st = $conn->prepare($sql);
  if ($types) $st->bind_param($types, ...$params);
  $st->execute(); $res = $st->get_result();
  if ($row = $res->fetch_assoc()) echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
  else { http_response_code(404); echo json_encode(["success"=>false,"message"=>"Không tìm thấy thiết bị"], JSON_UNESCAPED_UNICODE); }
  $st->close(); $conn->close(); exit;
}

$q = "SELECT i.MaIoT, i.LoaiCamBien, i.GiaTriDo, i.DonVi, i.ThoiGianDo, i.TrangThai,
             i.MaVung, i.CanhBaoNguyen, v.TenVung
      FROM thietbiIoT i JOIN vungtrong v ON i.MaVung=v.MaVung
      ORDER BY i.MaIoT";
$res = $conn->query($q); $data=[]; while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
