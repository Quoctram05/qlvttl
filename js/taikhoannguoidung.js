const API = {
  list:   "/nhom16/qlvttl/php/api/nguoidung/api_hien_thi_tai_khoan_nguoi_dung.php",
  add:    "/nhom16/qlvttl/php/api/nguoidung/api_them_tai_khoan_nguoi_dung.php",
  update: "/nhom16/qlvttl/php/api/nguoidung/api_sua_tai_khoan_nguoi_dung.php",
  remove: "/nhom16/qlvttl/php/api/nguoidung/api_xoa_tai_khoan_nguoi_dung.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const tbody = $("#taikhoan-table tbody");
    tbody.empty();
    res.data.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaTaiKhoan}</td>
          <td>${row.TenDangNhap}</td>
          <td>${row.VaiTro}</td>
          <td>${row.TrangThai}</td>
          <td>${row.NgayDangKy}</td>
          <td>${row.MaHo || ''}</td>
          <td>${row.Email || ''}</td>
        </tr>
      `);
      tr.click(() => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaTaiKhoan").val(row.MaTaiKhoan).prop("readonly", true);
  $("#TenDangNhap").val(row.TenDangNhap);
  $("#MatKhau").val(""); // Không hiển thị mật khẩu đã mã hóa
  $("#VaiTro").val(row.VaiTro);
  $("#TrangThai").val(row.TrangThai);
  $("#NgayDangKy").val(row.NgayDangKy);
  $("#MaHo").val(row.MaHo);
  $("#Email").val(row.Email);
}

function thuThapForm() {
  return {
    MaTaiKhoan: $("#MaTaiKhoan").val().trim(),
    TenDangNhap: $("#TenDangNhap").val().trim(),
    MatKhau: $("#MatKhau").val(),
    VaiTro: $("#VaiTro").val(),
    TrangThai: $("#TrangThai").val(),
    NgayDangKy: $("#NgayDangKy").val(),
    MaHo: $("#MaHo").val(),
    Email: $("#Email").val()
  };
}

function resetForm() {
  $("#taikhoan-form")[0].reset();
  $("#MaTaiKhoan").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã thêm tài khoản");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể thêm");
      console.error("Thêm lỗi:", xhr.responseText);
    }
  });
}

function sua(data) {
  $.ajax({
    url: API.update,
    method: "PUT",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã cập nhật");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể cập nhật");
      console.error("Sửa lỗi:", xhr.responseText);
    }
  });
}

function xoa(ma) {
  $.ajax({
    url: `${API.remove}?MaTaiKhoan=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể xoá");
      console.error("Xoá lỗi:", xhr.responseText);
    }
  });
}

$("#taikhoan-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaTaiKhoan").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").click(() => {
  const ma = $("#MaTaiKhoan").val();
  if (!ma) return alert("Chọn tài khoản cần xoá");
  if (!confirm("Xác nhận xoá tài khoản " + ma + "?")) return;
  xoa(ma);
});

$(document).ready(taiDanhSach);
