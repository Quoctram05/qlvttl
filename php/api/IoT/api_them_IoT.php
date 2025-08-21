<?php
include_once __DIR__ . '/../../connect.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["success"=>false,"error"=>"METHOD_NOT_ALLOWED"], JSON_UNESCAPED_UNICODE); exit;
}

/* đọc body một lần, fallback $_POST */
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data) || empty($data)) $data = $_POST;

/* chuẩn hoá key */
$in=[]; foreach ($data as $k=>$v) $in[strtolower($k)]=$v;

/* chuẩn hoá mã (xoá NBSP & whitespace, upper) */
function norm_code($s){ $s=(string)$s; $s=str_replace("\xC2\xA0",' ',$s); $s=preg_replace('/\s+/u','',$s); return strtoupper($s); }

/* lấy dữ liệu */
$MaIoT        = norm_code($in['maiot']  ?? '');
$LoaiCamBien  = trim((string)($in['loaicambien'] ?? ''));
$GiaTriDo     = isset($in['giatrido']) ? trim((string)$in['giatrido']) : null;
$DonVi        = isset($in['donvi'])    ? trim((string)$in['donvi'])    : null;
$ThoiGianDo   = isset($in['thoigiando']) ? trim((string)$in['thoigiando']) : null;
$TrangThai    = isset($in['trangthai']) ? (int)$in['trangthai'] : null;
$MaVung       = norm_code($in['mavung'] ?? '');
$CanhBaoNguyen= isset($in['canhbaonguyen']) ? (int)$in['canhbaonguyen'] : 0;

/* bắt buộc */
$missing=[]; foreach(['MaIoT','LoaiCamBien','TrangThai','MaVung'] as $k){ if($$k===''||$$k===null)$missing[]=$k; }
if($missing){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }

/* validate */
if(!preg_match('/^[A-Z0-9]{1,5}$/',$MaIoT))  { echo json_encode(["success"=>false,"error"=>"INVALID_MAIOT"]);  exit; }
if(!preg_match('/^[A-Z0-9]{1,5}$/',$MaVung)) { echo json_encode(["success"=>false,"error"=>"INVALID_MAVUNG"]); exit; }
if(!in_array($TrangThai,[0,1,2],true))      { echo json_encode(["success"=>false,"error"=>"INVALID_TRANGTHAI"]); exit; }
if(!in_array($CanhBaoNguyen,[0,1],true))    { echo json_encode(["success"=>false,"error"=>"INVALID_CANHBAO"]);  exit; }

/* trùng MaIoT? */
$st=$conn->prepare("SELECT 1 FROM thietbiiot WHERE MaIoT=?");
$st->bind_param("s",$MaIoT); $st->execute(); $st->store_result();
if($st->num_rows>0){ $st->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_MAIOT"]); exit; }
$st->close();

/* FK MaVung tồn tại? */
$st=$conn->prepare("SELECT 1 FROM vungtrong WHERE MaVung=?");
$st->bind_param("s",$MaVung); $st->execute(); $st->store_result();
if($st->num_rows===0){ $st->close(); http_response_code(400); echo json_encode(["success"=>false,"error"=>"FK_MAVUNG_NOT_FOUND","got"=>$MaVung], JSON_UNESCAPED_UNICODE); exit; }
$st->close();

/* insert (1–n, không chặn trùng MaVung) */
$sql="INSERT INTO thietbiiot (MaIoT,LoaiCamBien,GiaTriDo,DonVi,ThoiGianDo,TrangThai,MaVung,CanhBaoNguyen)
      VALUES (?,?,?,?,?,?,?,?)";
$st=$conn->prepare($sql);
$st->bind_param("ssssiisi",$MaIoT,$LoaiCamBien,$GiaTriDo,$DonVi,$ThoiGianDo,$TrangThai,$MaVung,$CanhBaoNguyen);

if($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Thêm IoT thành công","data"=>[
    "MaIoT"=>$MaIoT,"LoaiCamBien"=>$LoaiCamBien,"GiaTriDo"=>$GiaTriDo,"DonVi"=>$DonVi,
    "ThoiGianDo"=>$ThoiGianDo,"TrangThai"=>$TrangThai,"MaVung"=>$MaVung,"CanhBaoNguyen"=>$CanhBaoNguyen
  ]], JSON_UNESCAPED_UNICODE);
}else{
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$st->error,
    "values"=>["MaIoT"=>$MaIoT,"MaVung"=>$MaVung,"MaVungHEX"=>bin2hex($MaVung)]
  ], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
