<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaVung = isset($_GET['MaVung']) ? trim($_GET['MaVung']) : null;
$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
if (!$MaVung && is_array($body) && isset($body['MaVung'])) $MaVung = trim($body['MaVung']);

if (!$MaVung){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAVUNG"], JSON_UNESCAPED_UNICODE); exit; }

/* Tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM vungtrong WHERE MaVung=? LIMIT 1");
$chk->bind_param("s",$MaVung); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* Xoá */
$stmt=$conn->prepare("DELETE FROM vungtrong WHERE MaVung=?");
$stmt->bind_param("s",$MaVung);
if ($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Đã xoá vùng trồng","deleted"=>$MaVung], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$stmt->error], JSON_UNESCAPED_UNICODE);
}
$stmt->close(); $conn->close();
