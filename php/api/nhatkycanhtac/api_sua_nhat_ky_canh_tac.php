<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../connect.php";

$in = json_decode(file_get_contents("php://input"), true) ?: [];

$MaNhatKy     = trim($in["MaNhatKy"]     ?? "");
$MaVung       = trim($in["MaVung"]       ?? "");
$MaHo         = trim($in["MaHo"]         ?? "");
$NgayThucHien = trim($in["NgayThucHien"] ?? "");
$HoatDong     = trim($in["HoatDong"]     ?? "");
$GhiChu       = trim($in["GhiChu"]       ?? "");

if ($MaNhatKy === "" || $MaVung === "" || $MaHo === "" || $NgayThucHien === "" || $HoatDong === "") {
  echo json_encode([
    "success" => false,
    "error" => "MISSING_FIELDS",
    "message" => "Thiếu thông tin bắt buộc để cập nhật"
  ]);
  exit;
}

$stmt = $conn->prepare("
  UPDATE nhatkycanhtac 
  SET MaVung=?, MaHo=?, NgayThucHien=?, HoatDong=?, GhiChu=?
  WHERE MaNhatKy=?
");
$stmt->bind_param("ssssss", $MaVung, $MaHo, $NgayThucHien, $HoatDong, $GhiChu, $MaNhatKy);

if ($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => "✅ Cập nhật thành công",
    "updated" => $stmt->affected_rows
  ]);
} else {
  echo json_encode([
    "success" => false,
    "error" => "UPDATE_FAILED",
    "message" => "❌ Không thể cập nhật",
    "debug" => $stmt->error
  ]);
}

$stmt->close();
$conn->close();
