<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../connect.php";

$sql = "SELECT * FROM nhatkycanhtac ORDER BY NgayThucHien DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode([
  "success" => true,
  "data" => $data
]);
