<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE); exit;
}

$MaThietBi = isset($_GET['MaThietBi']) ? trim($_GET['MaThietBi']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!is_array($b)) $b = [];
if (!$MaThietBi && isset($b['MaThietBi'])) $MaThietBi = trim($b['MaThietBi']);

if (!$MaThietBi){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MATHIETBI"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM thietbimaymoc WHERE MaThietBi=? LIMIT 1");
$chk->bind_param("s",$MaThietBi); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* build update */
$fields=[]; $types=""; $params=[];

if (isset($b['TenThietBi']) && $b['TenThietBi']!==""){ $fields[]="TenThietBi=?"; $types.="s"; $params[]=trim($b['TenThietBi']); }
if (isset($b['LoaiThietBi']) && $b['LoaiThietBi']!==""){ $fields[]="LoaiThietBi=?"; $types.="s"; $params[]=trim($b['LoaiThietBi']); }
if (array_key_exists('TrangThai',$b)) {
  $t=(int)$b['TrangThai'];
  if (!in_array($t,[0,1,2],true)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_TRANGTHAI"], JSON_UNESCAPED_UNICODE); exit; }
  $fields[]="TrangThai=?"; $types.="i"; $params[]=$t;
}
if (array_key_exists('NgayNhap',$b)) {
  if ($b['NgayNhap']==="" || $b['NgayNhap']===null){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE","message"=>"NgayNhap không được rỗng"], JSON_UNESCAPED_UNICODE); exit; }
  $ns=str_replace('/','-',trim($b['NgayNhap'])); $ts=strtotime($ns);
  if ($ts===false){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE"], JSON_UNESCAPED_UNICODE); exit; }
  $ns=date('Y-m-d',$ts);
  $fields[]="NgayNhap=?"; $types.="s"; $params[]=$ns;
}
if (array_key_exists('NhaSanXuat',$b)) { $fields[]="NhaSanXuat=?"; $types.="s"; $params[]=(string)$b['NhaSanXuat']; }

if (empty($fields)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS"], JSON_UNESCAPED_UNICODE); exit; }

$sql="UPDATE thietbimaymoc SET ".implode(", ",$fields)." WHERE MaThietBi=?";
$types.="s"; $params[]=$MaThietBi;

$st=$conn->prepare($sql);
$st->bind_param($types, ...$params);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Cập nhật thiết bị thành công","updated"=>$st->affected_rows], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
