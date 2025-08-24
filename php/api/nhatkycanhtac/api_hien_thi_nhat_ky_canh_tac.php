<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../../connect.php';

$sql = "SELECT * FROM nhatkycanhtac";
$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
