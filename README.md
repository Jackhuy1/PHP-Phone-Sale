# Shop Điện Thoại - Website Bán Điện Thoại Bằng PHP

Website bán điện thoại đơn giản được xây dựng bằng PHP và MySQL.

## Cấu trúc dự án

```
/home/huy/php/
├── config.php              # Cấu hình database và session
├── index.php               # Trang chủ
├── product.php             # Chi tiết sản phẩm
├── cart.php                # Giỏ hàng
├── checkout.php            # Thanh toán
├── add_product.php         # Thêm sản phẩm (Admin)
├── admin.php               # Dashboard quản trị
├── login.php               # Đăng nhập
├── orders.php              # Xem đơn hàng
├── Pagination.php          # Class phân trang
├── database.sql            # Schema database
├── assets/
│   ├── css/
│   │   └── style.css       # CSS styling
│   └── js/
│       └── main.js         # JavaScript
└── uploads/                # Thư mục lưu ảnh (tùy chọn)
```

## Các tính năng

### 1. Trang chủ (index.php)
- Hiển thị danh sách sản phẩm theo danh mục
- Tìm kiếm sản phẩm
- Phân trang
- Thêm vào giỏ hàng

### 2. Chi tiết sản phẩm (product.php)
- Hiển thị thông tin chi tiết sản phẩm
- Hình ảnh, giá, mô tả
- Thêm vào giỏ hàng
- Cập nhật số lượng

### 3. Giỏ hàng (cart.php)
- Xem danh sách sản phẩm trong giỏ
- Thêm/xóa sản phẩm
- Cập nhật số lượng
- Tính tổng tiền

### 4. Thanh toán (checkout.php)
- Nhập thông tin giao hàng
- Chọn phương thức thanh toán
- Tạo đơn hàng

### 5. Quản trị (admin.php)
- Dashboard với thống kê
- Danh sách sản phẩm
- Danh sách đơn hàng
- Thêm/sửa/sửa sản phẩm

### 6. Đăng nhập (login.php)
- Đăng nhập bằng username/email và password
- Hỗ trợ admin và user

## Cài đặt

### 1. Yêu cầu
- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Apache/Nginx với mod_php

### 2. Cài đặt Database

1. Tạo database mới:
```sql
CREATE DATABASE shop_dienthoai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import file `database.sql`:
```bash
mysql -u root -p shop_dienthoai < database.sql
```

3. Cấu hình kết nối trong `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shop_dienthoai');
define('DB_USER', 'root');
define('DB_PASS', 'password');
```

### 3. Cấu hình PHP

Đảm bảo các extension sau đã bật trong `php.ini`:
- PDO
- PDO MySQL
- GD (cho xử lý ảnh)

### 4. Chạy website

1. Upload toàn bộ project vào thư mục web (ví dụ: `/var/www/html/shop`)
2. Truy cập: `http://localhost/shop/`

## Tài khoản mặc định

### Admin
- Username: `admin`
- Password: `admin123`

### User
- Username: `user`
- Password: `user123`

*(Lưu ý: Password được hash bằng password_hash() nên không thể đọc trực tiếp)*

## Dữ liệu mẫu

File `database.sql` đã có sẵn:
- 6 danh mục sản phẩm
- 12 sản phẩm mẫu
- 1 tài khoản admin

## Cấu trúc Database

### Bảng chính:

1. **categories** - Danh mục sản phẩm
2. **products** - Sản phẩm
3. **users** - Người dùng
4. **cart** - Giỏ hàng
5. **orders** - Đơn hàng
6. **order_items** - Chi tiết đơn hàng

### Quan hệ:
- products.category_id -> categories.id
- cart.user_id -> users.id
- cart.product_id -> products.id
- orders.user_id -> users.id
- order_items.order_id -> orders.id
- order_items.product_id -> products.id

## Bảo mật

- Password được hash bằng `password_verify()`
- Sử dụng PDO với prepared statements để tránh SQL injection
- Session quản lý đăng nhập
- Kiểm tra quyền truy cập admin

## Lưu ý

1. **Hình ảnh**: Hiện tại sử dụng URL từ database. Để upload ảnh thực tế, cần thêm logic upload vào `add_product.php`.

2. **Payment**: Hiện tại chỉ là demo, chưa tích hợp thanh toán thực tế.

3. **Email**: Chưa có chức năng gửi email xác nhận đơn hàng.

4. **Security**: Đây là bản demo cơ bản, không dùng cho sản xuất thực tế.

## License

MIT License
