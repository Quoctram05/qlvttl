const API = {
  list:   "/nhom16/qlvttl/php/api/vattu/api_hien_thi_vat_tu.php",
  add:    "/nhom16/qlvttl/php/api/vattu/api_them_vat_tu.php",
  update: "/nhom16/qlvttl/php/api/vattu/api_sua_vat_tu.php",
  delete: "/nhom16/qlvttl/php/api/vattu/api_xoa_vattu.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const tbody = $("#vattu-table tbody");
    tbody.empty();
    res.data.forEach(vt => {
      const tr = $(`
        <tr>
          <td>${vt.MaVatTu}</td>
          <td>${vt.TenVatTu}</td>
          <td>${vt.LoaiVatTu}</td>
          <td>${vt.DonViTinh}</td>
          <td>${vt.NgayNhap}</td>
        </tr>
      `);
      tr.click(() => fillForm(vt));
      tbody.append(tr);
    });
  });
}

function fillForm(vt) {
  $("#MaVatTu").val(vt.MaVatTu).prop("readonly", true);
  $("#TenVatTu").val(vt.TenVatTu);
  $("#LoaiVatTu").val(vt.LoaiVatTu);
  $("#DonViTinh").val(vt.DonViTinh);
  $("#NgayNhap").val(vt.NgayNhap);
}

function thuThapForm() {
  return {
    MaVatTu: $("#MaVatTu").val().trim(),
    TenVatTu: $("#TenVatTu").val().trim(),
    LoaiVatTu: $("#LoaiVatTu").val().trim(),
    DonViTinh: $("#DonViTinh").val().trim(),
    NgayNhap: $("#NgayNhap").val()
  };
}

function resetForm() {
  $("#vattu-form")[0].reset();
  $("#MaVatTu").prop("readonly", false);
}

function them(vt) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(vt),
    success: res => {
      alert(res.message || "Đã thêm vật tư");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể thêm vật tư");
      console.error(xhr.responseText);
    }
  });
}

function sua(vt) {
  $.ajax({
    url: API.update,
    method: "PUT",
    contentType: "application/json",
    data: JSON.stringify(vt),
    success: res => {
      alert(res.message || "Đã cập nhật vật tư");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể cập nhật");
      console.error(xhr.responseText);
    }
  });
}

function xoa(ma) {
  $.ajax({
    url: `${API.delete}?MaVatTu=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể xoá");
      console.error(xhr.responseText);
    }
  });
}

$("#vattu-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaVatTu").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").click(() => {
  const ma = $("#MaVatTu").val();
  if (!ma) return alert("Chọn vật tư cần xoá");
  if (!confirm("Xác nhận xoá vật tư " + ma + "?")) return;
  xoa(ma);
});

$(document).ready(taiDanhSach);
