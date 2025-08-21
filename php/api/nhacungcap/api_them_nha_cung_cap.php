<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE); exit;
}

$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!is_array($b) || empty($b)) $b = $_POST;

$MaNCC  = isset($b['MaNCC'])  ? trim($b['MaNCC'])  : null;
$TenNCC = isset($b['TenNCC']) ? trim($b['TenNCC']) : null;
$DiaChi = isset($b['DiaChi']) ? trim($b['DiaChi']) : null;
$SDT    = isset($b['SDT'])    ? trim($b['SDT'])    : null;
$Email  = isset($b['Email'])  ? trim($b['Email'])  : null;

$missing=[]; foreach(['MaNCC','TenNCC'] as $k) if (empty($$k)) $missing[]=$k;
if ($missing) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }
if (!preg_match('/^[A-Za-z0-9]{1,5}$/', $MaNCC)) { echo json_encode(["success"=>false,"error"=>"INVALID_MANCC","message"=>"MaNCC tối đa 5 ký tự chữ/số"], JSON_UNESCAPED_UNICODE); exit; }
if ($SDT !== null && $SDT !== "" && !preg_match('/^[0-9+\-\s]{7,20}$/', $SDT)) { echo json_encode(["success"=>false,"error"=>"INVALID_SDT"], JSON_UNESCAPED_UNICODE); exit; }
if ($Email !== null && $Email !== "" && !filter_var($Email, FILTER_VALIDATE_EMAIL)) { echo json_encode(["success"=>false,"error"=>"INVALID_EMAIL"], JSON_UNESCAPED_UNICODE); exit; }

/* trùng MaNCC? */
$st=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE MaNCC=? LIMIT 1");
$st->bind_param("s",$MaNCC); $st->execute(); $st->store_result();
if ($st->num_rows>0){ $st->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_MANCC"], JSON_UNESCAPED_UNICODE); exit; }
$st->close();

/* trùng SDT? (nếu có) */
if ($SDT !== null && $SDT !== "") {
  $st=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE SDT=? LIMIT 1");
  $st->bind_param("s",$SDT); $st->execute(); $st->store_result();
  if ($st->num_rows>0){ $st->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_SDT"], JSON_UNESCAPED_UNICODE); exit; }
  $st->close();
}
/* trùng Email? (nếu có) */
if ($Email !== null && $Email !== "") {
  $st=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE Email=? LIMIT 1");
  $st->bind_param("s",$Email); $st->execute(); $st->store_result();
  if ($st->num_rows>0){ $st->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_EMAIL"], JSON_UNESCAPED_UNICODE); exit; }
  $st->close();
}

/* insert */
$sql="INSERT INTO nhacungcapvattu (MaNCC, TenNCC, DiaChi, SDT, Email) VALUES (?,?,?,?,?)";
$st=$conn->prepare($sql);
$st->bind_param("sssss", $MaNCC,$TenNCC,$DiaChi,$SDT,$Email);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Thêm nhà cung cấp thành công",
    "data"=>compact('MaNCC','TenNCC','DiaChi','SDT','Email')], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
