<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../connect.php";

$MaNhatKy = $_GET["MaNhatKy"] ?? "";
$MaNhatKy = trim($MaNhatKy);

if ($MaNhatKy === "") {
  echo json_encode([
    "success" => false,
    "error" => "MISSING_ID",
    "message" => "Thiếu mã nhật ký để xoá"
  ]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM nhatkycanhtac WHERE MaNhatKy = ?");
$stmt->bind_param("s", $MaNhatKy);

if ($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => "🗑️ Đã xoá nhật ký canh tác",
    "deleted" => $stmt->affected_rows
  ]);
} else {
  echo json_encode([
    "success" => false,
    "error" => "DELETE_FAILED",
    "message" => "❌ Không thể xoá dữ liệu",
    "debug" => $stmt->error
  ]);
}

$stmt->close();
$conn->close();
