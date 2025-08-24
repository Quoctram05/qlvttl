// ==== CẤU HÌNH ==== 
const ROOT = "/qlvttl/php/api";
const API = {
  list:   "/nhom16/qlvttl/php/api/honongdan/api_hien_thi_ho_nong_dan.php",
  add:    "/nhom16/qlvttl/php/api/honongdan/api_them_ho_nong_dan.php",
  update: "/nhom16/qlvttl/php/api/honongdan/api_sua_ho_nong_dan.php",
  remove: "/nhom16/qlvttl/php/api/honongdan/api_xoa_ho_nong_dan.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const ds = res.data || [];
    const tbody = $("#honongdan-table tbody");
    tbody.empty();
    ds.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaHo}</td>
          <td>${row.TenChuHo}</td>
          <td>${row.CCCD}</td>
          <td>${row.GioiTinh || ''}</td>
          <td>${row.NgaySinh || ''}</td>
          <td>${row.SDT || ''}</td>
          <td>${row.DiaChi || ''}</td>
        </tr>
      `);
      tr.on("click", () => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaHo").val(row.MaHo).prop("readonly", true);
  $("#TenChuHo").val(row.TenChuHo);
  $("#CCCD").val(row.CCCD);
  $("#GioiTinh").val(row.GioiTinh);
  $("#NgaySinh").val(row.NgaySinh);
  $("#SDT").val(row.SDT);
  $("#DiaChi").val(row.DiaChi);
}

function thuThapForm() {
  return {
    MaHo:      $("#MaHo").val()?.trim(),
    TenChuHo:  $("#TenChuHo").val()?.trim(),
    CCCD:      $("#CCCD").val()?.trim(),
    GioiTinh:  parseInt($("#GioiTinh").val()),
    NgaySinh:  $("#NgaySinh").val()?.trim(),
    SDT:       $("#SDT").val()?.trim(),
    DiaChi:    $("#DiaChi").val()?.trim()
  };
}

function resetForm() {
  $("#honongdan-form")[0].reset();
  $("#MaHo").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Thêm thành công");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Thêm lỗi:", xhr.responseText);
      alert("Không thể thêm dữ liệu");
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
      alert(res.message || "Cập nhật thành công");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Sửa lỗi:", xhr.responseText);
      alert("Không thể cập nhật");
    }
  });
}

function xoa(maho) {
  $.ajax({
    url: `${API.remove}?MaHo=${encodeURIComponent(maho)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Xoá lỗi:", xhr.responseText);
      alert("Không thể xoá dữ liệu");
    }
  });
}

$("#honongdan-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaHo").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").on("click", function () {
  const MaHo = $("#MaHo").val();
  if (!MaHo) return alert("Chọn hộ cần xoá.");
  if (!confirm(`Xác nhận xoá hộ ${MaHo}?`)) return;
  xoa(MaHo);
});

$(document).ready(taiDanhSach);
