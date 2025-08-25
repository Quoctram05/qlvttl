$(document).ready(function () {
  const user = JSON.parse(localStorage.getItem("auth_user") || "{}");
  const maHo = user.MaHo;

  if (!maHo) {
    $("#error-msg").text("Không tìm thấy mã hộ từ tài khoản đăng nhập.");
    return;
  }

  $.getJSON(`/nhom16/qlvttl/php/api/honongdan/api_thong_tin_ho.php?MaHo=${encodeURIComponent(maHo)}`, function (res) {
    if (res.success) {
      const data = res.data;
      $("#MaHo").text(data.MaHo);
      $("#TenChuHo").text(data.TenChuHo);
      $("#CCCD").text(data.CCCD);
      $("#GioiTinh").text(data.GioiTinh === 0 ? "Nam" : "Nữ");
      $("#NgaySinh").text(data.NgaySinh);
      $("#SDT").text(data.SDT);
      $("#DiaChi").text(data.DiaChi);
    } else {
      $("#error-msg").text(res.error || "Không thể tải thông tin.");
    }
  }).fail(function () {
    $("#error-msg").text("Lỗi kết nối tới máy chủ.");
  });
});
