// ==== CẤU HÌNH API ====
const API = {
  login: "/qlvttl/php/api/taikhoan/api_dang_nhap.php",
  register: "/qlvttl/php/api/taikhoan/api_dang_ky.php"
};

// ==== CHUYỂN TAB (login / register) ====
function showTab(tab) {
  $("#login-form").css("display", tab === "login" ? "flex" : "none");
  $("#register-form").css("display", tab === "register" ? "flex" : "none");

  $(".tab-btn").removeClass("active");
  $(`.tab-btn.${tab}`).addClass("active");

  $("#login-message").text("");
  $("#register-message").text("");
}

// ==== ĐĂNG NHẬP ====
$("#login-form").on("submit", function (e) {
  e.preventDefault();

  const TenDangNhap = $("#login-username").val().trim();
  const MatKhau = $("#login-password").val();
  const msg = $("#login-message");

  if (!TenDangNhap || !MatKhau) {
    msg.css("color", "red").text("Vui lòng nhập đầy đủ thông tin");
    return;
  }

  msg.css("color", "#0a8").text("Đang đăng nhập…");

  $.ajax({
    url: API.login,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({ TenDangNhap, MatKhau }),
    success: function (res) {
      if (!res.success) {
        msg.css("color", "red").text(res.message || res.error || "Đăng nhập thất bại");
        return;
      }

      // Lưu thông tin đăng nhập vào localStorage
      localStorage.setItem("auth_token", res.token || "");
      localStorage.setItem("auth_user", JSON.stringify(res.user || {}));

      msg.css("color", "green").text("Đăng nhập thành công! Đang chuyển…");

      setTimeout(() => {
        const role = (res.user?.VaiTro || "").trim().toLowerCase();
        window.location.href = (role === "admin")
          ? "/qlvttl/php/admin/index.php?p=dashboard"
          : "/qlvttl/index.html";
      }, 1000);
    },
    error: function (xhr) {
      console.error("Lỗi đăng nhập:", xhr.responseText);
      msg.css("color", "red").text("Không thể đăng nhập");
    }
  });
});

// ==== ĐĂNG KÝ ====
$("#register-form").on("submit", function (e) {
  e.preventDefault();

  const TenDangNhap = $("#reg-username").val().trim();
  const MaHo = $("#reg-maho").val().trim();
  const Email = $("#reg-email").val().trim();
  const MatKhau = $("#reg-password").val();
  const repass = $("#reg-repassword").val();
  const msg = $("#register-message");

  if (!TenDangNhap || !MaHo || !Email || !MatKhau || !repass) {
    msg.css("color", "red").text("Vui lòng nhập đầy đủ thông tin");
    return;
  }

  if (MatKhau !== repass) {
    msg.css("color", "red").text("Mật khẩu nhập lại không khớp");
    return;
  }

  msg.css("color", "#0a8").text("Đang tạo tài khoản…");

  $.ajax({
    url: API.register,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      TenDangNhap,
      MatKhau,
      MaHo,
      Email
    }),
    success: function (res) {
      if (!res.success) {
        msg.css("color", "red").text(res.message || res.error || "Đăng ký thất bại");
        return;
      }

      msg.css("color", "green").text("Đăng ký thành công! Bạn có thể đăng nhập.");

      setTimeout(() => {
        showTab("login");
        $("#login-username").val(TenDangNhap);
        $("#login-password").val(MatKhau);
      }, 1000);
    },
    error: function (xhr) {
      console.error("Lỗi đăng ký:", xhr.responseText);
      msg.css("color", "red").text("Không thể đăng ký");
    }
  });
});
