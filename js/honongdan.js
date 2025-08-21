const API = "http://localhost:8080/qlvttl/php/api/honongdan/";

function taiDanhSach() {
  $.get(API + "api_hien_thi_ho_nong_dan.php", function (res) {
    const ds = res.data || [];
    const tbody = $("#honongdan-table tbody").empty();
    ds.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaHo}</td>
          <td>${row.TenChuHo}</td>
          <td>${row.CCCD}</td>
          <td>${row.NgaySinh}</td>
          <td>${row.SoDienThoai}</td>
          <td>${row.DiaChi}</td>
        </tr>
      `);
      tr.click(() => doFillForm(row)); // click để chọn và sửa
      tbody.append(tr);
    });
  });
}

function doFillForm(row) {
  $("#MaHo").val(row.MaHo).prop("readonly", true);
  $("#TenChuHo").val(row.TenChuHo);
  $("#CCCD").val(row.CCCD);
  $("#NgaySinh").val(row.NgaySinh);
  $("#SoDienThoai").val(row.SoDienThoai);
  $("#DiaChi").val(row.DiaChi);
}

$("#honongdan-form").submit(function (e) {
  e.preventDefault();
  const data = {
    MaHo: $("#MaHo").val(),
    TenChuHo: $("#TenChuHo").val(),
    CCCD: $("#CCCD").val(),
    NgaySinh: $("#NgaySinh").val(),
    SoDienThoai: $("#SoDienThoai").val(),
    DiaChi: $("#DiaChi").val()
  };

  const isSua = $("#MaHo").prop("readonly");

  $.ajax({
    url: API + (isSua ? "api_sua_ho_nong_dan.php" : "api_them_ho_nong_dan.php"),
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (res) {
      alert(res.message || (isSua ? "Đã cập nhật" : "Đã thêm"));
      $("#honongdan-form")[0].reset();
      $("#MaHo").prop("readonly", false);
      taiDanhSach();
    },
    error: () => alert("Lỗi khi gửi yêu cầu.")
  });
});

$("#btn-xoa").click(function () {
  const MaHo = $("#MaHo").val();
  if (!MaHo) return alert("Vui lòng chọn hộ để xoá.");

  if (!confirm(`Xác nhận xoá hộ ${MaHo}?`)) return;

  $.get(API + "api_xoa_ho_nong_dan.php?MaHo=" + MaHo, function (res) {
    alert(res.message || "Đã xoá");
    $("#honongdan-form")[0].reset();
    $("#MaHo").prop("readonly", false);
    taiDanhSach();
  });
});

$(document).ready(taiDanhSach);
