const API = {
  list:   "/nhom16/qlvttl/php/api/maymoc/api_hien_thi_may_moc.php",
  add:    "/nhom16/qlvttl/php/api/maymoc/api_them_may_moc.php",
  update: "/nhom16/qlvttl/php/api/maymoc/api_sua_may_moc.php",
  remove: "/nhom16/qlvttl/php/api/maymoc/api_xoa_may_moc.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const tbody = $("#tb-table tbody");
    tbody.empty();
    res.data.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaThietBi}</td>
          <td>${row.TenThietBi}</td>
          <td>${row.LoaiThietBi}</td>
          <td>${row.TrangThai}</td>
          <td>${row.NgayNhap}</td>
          <td>${row.NhaSanXuat}</td>
        </tr>
      `);
      tr.click(() => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaThietBi").val(row.MaThietBi).prop("readonly", true);
  $("#TenThietBi").val(row.TenThietBi);
  $("#LoaiThietBi").val(row.LoaiThietBi);
  $("#TrangThai").val(row.TrangThai);
  $("#NgayNhap").val(row.NgayNhap);
  $("#NhaSanXuat").val(row.NhaSanXuat);
}

function thuThapForm() {
  return {
    MaThietBi:    $("#MaThietBi").val()?.trim(),
    TenThietBi:   $("#TenThietBi").val()?.trim(),
    LoaiThietBi:  $("#LoaiThietBi").val()?.trim(),
    TrangThai:    parseInt($("#TrangThai").val()),
    NgayNhap:     $("#NgayNhap").val(),
    NhaSanXuat:   $("#NhaSanXuat").val()?.trim()
  };
}

function resetForm() {
  $("#tb-form")[0].reset();
  $("#MaThietBi").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã thêm thiết bị");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi thêm:", xhr.responseText);
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
      alert(res.message || "Đã cập nhật thiết bị");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi sửa:", xhr.responseText);
      alert("Không thể cập nhật");
    }
  });
}

function xoa(ma) {
  $.ajax({
    url: `${API.remove}?MaThietBi=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá thiết bị");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi xoá:", xhr.responseText);
      alert("Không thể xoá dữ liệu");
    }
  });
}

$("#tb-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaThietBi").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").click(function () {
  const ma = $("#MaThietBi").val();
  if (!ma) return alert("Chọn thiết bị cần xoá.");
  if (!confirm("Xoá thiết bị " + ma + "?")) return;
  xoa(ma);
});

$(document).ready(taiDanhSach);
