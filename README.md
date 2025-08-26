<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

<h1 align="center">MG Fashion Shop</h1>
<p align="center">
  <b>Website bán hàng thời trang hiện đại, chuyên nghiệp, thân thiện với người dùng</b><br>
  <a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/badge/Laravel-Framework-red?logo=laravel"></a>
  <a href="https://react.dev/" target="_blank"><img src="https://img.shields.io/badge/React-Frontend-blue?logo=react"></a>
  <a href="https://getbootstrap.com/" target="_blank"><img src="https://img.shields.io/badge/Bootstrap-UI-purple?logo=bootstrap"></a>
</p>

---

## 🛍️ Giới thiệu

**MG Fashion Shop** là website thương mại điện tử chuyên về thời trang, được xây dựng với mục tiêu mang lại trải nghiệm mua sắm trực tuyến tiện lợi, nhanh chóng và an toàn cho khách hàng.

Website sử dụng công nghệ **Laravel** (backend) và **ReactJS** (frontend), kết hợp với giao diện hiện đại từ **Bootstrap**.

---

## 🚀 Tính năng nổi bật

- **Quản lý sản phẩm:** Duyệt, tìm kiếm, lọc sản phẩm theo danh mục, giá, size, màu sắc.
- **Giỏ hàng thông minh:** Thêm/xóa/cập nhật sản phẩm, chọn nhiều sản phẩm, ghi chú, tính tổng tiền tự động.
- **Thanh toán trực tuyến:** Tích hợp VNPay, Momo, COD, xác nhận đơn hàng qua email.
- **Quản lý đơn hàng:** Theo dõi trạng thái, yêu cầu hoàn đơn, đánh giá sản phẩm sau khi nhận hàng.
- **Quản lý tài khoản:** Đăng ký, đăng nhập, cập nhật thông tin cá nhân, quản lý địa chỉ giao hàng.
- **Trang admin:** Quản lý sản phẩm, đơn hàng, voucher, người dùng, báo cáo doanh thu, đánh giá.
- **Giao diện thân thiện:** Responsive, tối ưu cho cả desktop và mobile.

---

## 📸 Demo giao diện

<p align="center">
  <img src="https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=800&q=80" width="400" alt="Demo MG Fashion">
</p>

---

## ⚙️ Công nghệ sử dụng

- **Backend:** Laravel 10.x, MySQL
- **Frontend:** ReactJS, Bootstrap 5
- **Thanh toán:** VNPay, Momo
- **Realtime:** Laravel Echo, Pusher
- **Email:** SMTP, Mailgun

---

## 📦 Hướng dẫn cài đặt

### 1. Clone dự án

```bash
git clone https://github.com/your-username/du_an_tot_nghiep.git
cd du_an_tot_nghiep
```

### 2. Cài đặt backend Laravel

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 3. Cài đặt frontend ReactJS

```bash
cd fashion-shop
npm install
npm start
```

---

## 💡 Đóng góp & liên hệ

- Nếu bạn có ý kiến đóng góp, vui lòng gửi về [trang liên hệ](./fashion-shop/src/pages/ContactPage.js) hoặc email: contact@mgfashion.com
- Tác giả: **MG Fashion Team**

---

<p align="center">
  <b>Cảm ơn bạn đã ghé thăm MG Fashion Shop!</b><br>
  <i>Chúc bạn có trải nghiệm mua sắm tuyệt vời!</i>
</p>