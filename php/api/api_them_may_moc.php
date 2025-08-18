<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE); exit;
}

$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!is_array($b) || empty($b)) $b = $_POST;

$MaThietBi   = isset($b['MaThietBi']) ? trim($b['MaThietBi']) : null;
$TenThietBi  = isset($b['TenThietBi']) ? trim($b['TenThietBi']) : null;
$LoaiThietBi = isset($b['LoaiThietBi']) ? trim($b['LoaiThietBi']) : null;
$TrangThai   = isset($b['TrangThai']) ? (int)$b['TrangThai'] : null;   // 0/1/2
$NgayNhap    = isset($b['NgayNhap']) ? trim($b['NgayNhap']) : null;    // YYYY-MM-DD hoặc DD/MM/YYYY
$NhaSanXuat  = isset($b['NhaSanXuat']) ? trim($b['NhaSanXuat']) : null;

$missing=[]; foreach (['MaThietBi','TenThietBi','LoaiThietBi','TrangThai','NgayNhap'] as $k) if(!isset($b[$k]) || $b[$k]==='') $missing[]=$k;
if ($missing){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }
if (!preg_match('/^[A-Za-z0-9]{1,5}$/', $MaThietBi)){ echo json_encode(["success"=>false,"error"=>"INVALID_MATHIETBI"], JSON_UNESCAPED_UNICODE); exit; }
if (!in_array($TrangThai,[0,1,2],true)){ echo json_encode(["success"=>false,"error"=>"INVALID_TRANGTHAI"], JSON_UNESCAPED_UNICODE); exit; }

$NgayNhap = str_replace('/','-',$NgayNhap); $ts = strtotime($NgayNhap);
if ($ts === false){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE"], JSON_UNESCAPED_UNICODE); exit; }
$NgayNhap = date('Y-m-d', $ts);

/* trùng mã? */
$st=$conn->prepare("SELECT 1 FROM thietbimaymoc WHERE MaThietBi=? LIMIT 1");
$st->bind_param("s", $MaThietBi); $st->execute(); $st->store_result();
if ($st->num_rows>0){ $st->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_MATHIETBI"], JSON_UNESCAPED_UNICODE); exit; }
$st->close();

/* insert */
$sql = "INSERT INTO thietbimaymoc (MaThietBi, TenThietBi, LoaiThietBi, TrangThai, NgayNhap, NhaSanXuat)
        VALUES (?,?,?,?,?,?)";
$st = $conn->prepare($sql);
$st->bind_param("sssiss", $MaThietBi, $TenThietBi, $LoaiThietBi, $TrangThai, $NgayNhap, $NhaSanXuat);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Thêm thiết bị thành công",
    "data"=>compact('MaThietBi','TenThietBi','LoaiThietBi','TrangThai','NgayNhap','NhaSanXuat')
  ], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
