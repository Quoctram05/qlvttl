<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE); exit;
}

$MaIoT = isset($_GET['MaIoT']) ? trim($_GET['MaIoT']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!is_array($b)) $b = [];
if (!$MaIoT && isset($b['MaIoT'])) $MaIoT = trim($b['MaIoT']);
if (!$MaIoT){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAIOT"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM thietbiiot WHERE MaIoT=? LIMIT 1");
$chk->bind_param("s",$MaIoT); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* build update */
$fields=[]; $types=""; $params=[];

if (isset($b['LoaiCamBien']) && $b['LoaiCamBien']!==""){ $fields[]="LoaiCamBien=?"; $types.="s"; $params[]=trim($b['LoaiCamBien']); }
if (array_key_exists('GiaTriDo',$b)) { $fields[]="GiaTriDo=?"; $types.="s"; $params[]=(string)$b['GiaTriDo']; }
if (array_key_exists('DonVi',$b))    { $fields[]="DonVi=?";    $types.="s"; $params[]=(string)$b['DonVi']; }
if (array_key_exists('ThoiGianDo',$b)){ $fields[]="ThoiGianDo=?"; $types.="s"; $params[]=(string)$b['ThoiGianDo']; }
if (array_key_exists('TrangThai',$b)) {
  $t = (int)$b['TrangThai'];
  if (!in_array($t,[0,1,2],true)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_TRANGTHAI"], JSON_UNESCAPED_UNICODE); exit; }
  $fields[]="TrangThai=?"; $types.="i"; $params[]=$t;
}
if (array_key_exists('CanhBaoNguyen',$b)) {
  $cb = (int)$b['CanhBaoNguyen'];
  if (!in_array($cb,[0,1],true)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_CANHBAO"], JSON_UNESCAPED_UNICODE); exit; }
  $fields[]="CanhBaoNguyen=?"; $types.="i"; $params[]=$cb;
}
/* đổi MaVung? kiểm tra 1–1 */
if (array_key_exists('MaVung',$b)) {
  if ($b['MaVung']==="" || $b['MaVung']===null){
    http_response_code(400);
    echo json_encode(["success"=>false,"error"=>"INVALID_MAVUNG","message"=>"MaVung không được rỗng"], JSON_UNESCAPED_UNICODE); exit;
  }
  $mv = trim($b['MaVung']);
  if (!preg_match('/^[A-Za-z0-9]{1,5}$/',$mv)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_MAVUNG"], JSON_UNESCAPED_UNICODE); exit; }

  /* tồn tại vùng? */
  $s=$conn->prepare("SELECT 1 FROM vungtrong WHERE MaVung=? LIMIT 1");
  $s->bind_param("s",$mv); $s->execute(); $s->store_result();
  if ($s->num_rows===0){ $s->close(); http_response_code(400); echo json_encode(["success"=>false,"error"=>"FK_MAVUNG_NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
  $s->close();

  /* vùng đã có IoT khác? */
  $s=$conn->prepare("SELECT 1 FROM thietbiiot WHERE MaVung=? AND MaIoT<>? LIMIT 1");
  $s->bind_param("ss",$mv,$MaIoT); $s->execute(); $s->store_result();
  if ($s->num_rows>0){ $s->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"MAVUNG_TAKEN"], JSON_UNESCAPED_UNICODE); exit; }
  $s->close();

  $fields[]="MaVung=?"; $types.="s"; $params[]=$mv;
}

if (empty($fields)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS"], JSON_UNESCAPED_UNICODE); exit; }

$sql="UPDATE thietbiiot SET ".implode(", ",$fields)." WHERE MaIoT=?";
$types.="s"; $params[]=$MaIoT;

$st=$conn->prepare($sql);
$st->bind_param($types, ...$params);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Cập nhật IoT thành công","updated"=>$st->affected_rows], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
