<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE); exit;
}

$MaNCC = isset($_GET['MaNCC']) ? trim($_GET['MaNCC']) : null;
$raw = file_get_contents("php://input"); $b = json_decode($raw, true);
if (!is_array($b)) $b = [];
if (!$MaNCC && isset($b['MaNCC'])) $MaNCC = trim($b['MaNCC']);
if (!$MaNCC) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MANCC"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE MaNCC=?"); $chk->bind_param("s",$MaNCC);
$chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* build update */
$fields=[]; $types=""; $params=[];

if (isset($b['TenNCC']) && $b['TenNCC']!==""){ $fields[]="TenNCC=?"; $types.="s"; $params[]=trim($b['TenNCC']); }
if (array_key_exists('DiaChi',$b)) { $fields[]="DiaChi=?"; $types.="s"; $params[]=(string)$b['DiaChi']; }

if (array_key_exists('SDT',$b)) {
  $SDT = trim((string)$b['SDT']);
  if ($SDT!=="" && !preg_match('/^[0-9+\-\s]{7,20}$/', $SDT)) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_SDT"], JSON_UNESCAPED_UNICODE); exit; }
  if ($SDT==="") { $fields[]="SDT=NULL"; } else {
    $s=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE SDT=? AND MaNCC<>? LIMIT 1");
    $s->bind_param("ss",$SDT,$MaNCC); $s->execute(); $s->store_result();
    if ($s->num_rows>0){ $s->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_SDT"], JSON_UNESCAPED_UNICODE); exit; }
    $s->close();
    $fields[]="SDT=?"; $types.="s"; $params[]=$SDT;
  }
}

if (array_key_exists('Email',$b)) {
  $Email = trim((string)$b['Email']);
  if ($Email!=="" && !filter_var($Email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_EMAIL"], JSON_UNESCAPED_UNICODE); exit; }
  if ($Email==="") { $fields[]="Email=NULL"; } else {
    $s=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE Email=? AND MaNCC<>? LIMIT 1");
    $s->bind_param("ss",$Email,$MaNCC); $s->execute(); $s->store_result();
    if ($s->num_rows>0){ $s->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_EMAIL"], JSON_UNESCAPED_UNICODE); exit; }
    $s->close();
    $fields[]="Email=?"; $types.="s"; $params[]=$Email;
  }
}

if (empty($fields)) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS"], JSON_UNESCAPED_UNICODE); exit; }

$sql="UPDATE nhacungcapvattu SET ".implode(", ",$fields)." WHERE MaNCC=?";
$types.="s"; $params[]=$MaNCC;

$st=$conn->prepare($sql);
$st->bind_param($types, ...$params);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Cập nhật nhà cung cấp thành công","updated"=>$st->affected_rows], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
