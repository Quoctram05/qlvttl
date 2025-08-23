const API = {
  list:   "/qlvttl/php/api/nhacungcap/api_hien_thi_nha_cung_cap.php",
  add:    "/qlvttl/php/api/nhacungcap/api_them_nha_cung_cap.php",
  update: "/qlvttl/php/api/nhacungcap/api_sua_nha_cung_cap.php",
  remove: "/qlvttl/php/api/nhacungcap/api_xoa_nha_cung_cap.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const tbody = $("#ncc-table tbody");
    tbody.empty();
    res.data.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaNCC}</td>
          <td>${row.TenNCC}</td>
          <td>${row.DiaChi}</td>
          <td>${row.SDT}</td>
          <td>${row.Email}</td>
        </tr>
      `);
      tr.click(() => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaNCC").val(row.MaNCC).prop("readonly", true);
  $("#TenNCC").val(row.TenNCC);
  $("#DiaChi").val(row.DiaChi);
  $("#SDT").val(row.SDT);
  $("#Email").val(row.Email);
}

function thuThapForm() {
  return {
    MaNCC: $("#MaNCC").val()?.trim(),
    TenNCC: $("#TenNCC").val()?.trim(),
    DiaChi: $("#DiaChi").val()?.trim(),
    SDT: $("#SDT").val()?.trim(),
    Email: $("#Email").val()?.trim()
  };
}

function resetForm() {
  $("#ncc-form")[0].reset();
  $("#MaNCC").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã thêm NCC");
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
      alert(res.message || "Cập nhật thành công");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi sửa:", xhr.responseText);
      alert("Không thể cập nhật");
    }
  });
}

function xoa(maNCC) {
  $.ajax({
    url: `${API.remove}?MaNCC=${encodeURIComponent(maNCC)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi xoá:", xhr.responseText);
      alert("Không thể xoá dữ liệu");
    }
  });
}

$("#ncc-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaNCC").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").click(function () {
  const MaNCC = $("#MaNCC").val();
  if (!MaNCC) return alert("Chọn NCC cần xoá.");
  if (!confirm("Xoá nhà cung cấp " + MaNCC + "?")) return;
  xoa(MaNCC);
});

$(document).ready(taiDanhSach);
