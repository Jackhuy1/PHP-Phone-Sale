-- =====================================================
-- Cấu trúc Database cho Website Bán Điện Thoại
-- =====================================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS shop_dienthoai 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE shop_dienthoai;

-- =====================================================
-- Bảng Categories (Danh mục sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT 'Tên danh mục',
    description TEXT COMMENT 'Mô tả danh mục',
    image VARCHAR(255) COMMENT 'URL hình ảnh',
    display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Bảng Products (Sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(32) PRIMARY KEY COMMENT 'ID sản phẩm (UUID)',
    name VARCHAR(200) NOT NULL COMMENT 'Tên sản phẩm',
    price DECIMAL(15, 2) NOT NULL COMMENT 'Giá tiền',
    original_price DECIMAL(15, 2) DEFAULT NULL COMMENT 'Giá gốc (giảm giá)',
    image VARCHAR(255) NOT NULL COMMENT 'URL hình ảnh sản phẩm',
    description TEXT COMMENT 'Mô tả chi tiết sản phẩm',
    stock INT DEFAULT 0 COMMENT 'Số lượng tồn kho',
    category_id INT COMMENT 'ID danh mục',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Bảng Users (Người dùng)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE COMMENT 'Tên đăng nhập',
    email VARCHAR(150) NOT NULL UNIQUE COMMENT 'Email',
    password VARCHAR(255) NOT NULL COMMENT 'Mật khẩu (hash)',
    role ENUM('user', 'admin') DEFAULT 'user' COMMENT 'Vai trò',
    profile_image VARCHAR(255) DEFAULT NULL COMMENT 'Hình ảnh đại diện',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Bảng Cart (Giỏ hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT COMMENT 'ID người dùng',
    product_id VARCHAR(32) COMMENT 'ID sản phẩm',
    quantity INT DEFAULT 1 COMMENT 'Số lượng',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Bảng Orders (Đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(32) PRIMARY KEY COMMENT 'ID đơn hàng (UUID)',
    user_id INT COMMENT 'ID người đặt hàng',
    total_amount DECIMAL(15, 2) NOT NULL COMMENT 'Tổng tiền',
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending' COMMENT 'Trạng thái',
    address TEXT COMMENT 'Địa chỉ giao hàng',
    phone VARCHAR(20) COMMENT 'Số điện thoại',
    payment_method ENUM('cash', 'bank_transfer', 'vnpay') DEFAULT 'cash' COMMENT 'Phương thức thanh toán',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Bảng Order Items (Chi tiết đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(32) COMMENT 'ID đơn hàng',
    product_id VARCHAR(32) COMMENT 'ID sản phẩm',
    quantity INT NOT NULL COMMENT 'Số lượng',
    price DECIMAL(15, 2) NOT NULL COMMENT 'Giá tại thời điểm đặt hàng',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Dữ liệu mẫu - Danh mục sản phẩm
-- =====================================================
INSERT INTO categories (name, description, display_order) VALUES
('iPhone', 'Điện thoại Apple iPhone', 1),
('Samsung', 'Điện thoại Samsung Galaxy', 2),
('Xiaomi', 'Điện thoại Xiaomi', 3),
('Vivo', 'Điện thoại Vivo', 4),
('Oppo', 'Điện thoại Oppo', 5),
('Nokia', 'Điện thoại Nokia', 6);

-- =====================================================
-- Dữ liệu mẫu - Sản phẩm (iPhone)
-- =====================================================
INSERT INTO products (id, name, price, original_price, image, description, stock, category_id) VALUES
('iphone15pro', 'iPhone 15 Pro Max 256GB', 24999000, 26999000, 'images/iphone15pro.jpg', 'iPhone 15 Pro Max với chip A17 Pro, camera 48MP, màn hình Super Retina XDR, Titanium finish', 15, 1),
('iphone15', 'iPhone 15 128GB', 19999000, NULL, 'images/iphone15.jpg', 'iPhone 15 với chip A16 Bionic, camera 48MP chính, màn hình Dynamic Island, Dynamic Camera Control', 25, 1),
('iphone14', 'iPhone 14 128GB', 16999000, 17999000, 'images/iphone14.jpg', 'iPhone 14 với chip A15 Bionic, camera 12MP kép, màn hình Super Retina XDR', 30, 1);

-- =====================================================
-- Dữ liệu mẫu - Sản phẩm (Samsung)
-- =====================================================
INSERT INTO products (id, name, price, original_price, image, description, stock, category_id) VALUES
('s24ultra', 'Samsung S24 Ultra 512GB', 27999000, 29999000, 'images/s24ultra.jpg', 'Samsung S24 Ultra với chip Snapdragon 8 Gen 3, camera 200MP, màn hình Dynamic AMOLED 2X, S Pen', 10, 2),
('s24', 'Samsung S24 256GB', 21999000, NULL, 'images/s24.jpg', 'Samsung S24 với chip Exynos 2400, camera 50MP chính, màn hình Dynamic AMOLED 2X, 120Hz', 20, 2),
('s23', 'Samsung S23 256GB', 18999000, 19999000, 'images/s23.jpg', 'Samsung S23 với chip Exynos 2200, camera 50MP kép, màn hình Dynamic AMOLED 2X, 120Hz AMOLED', 25, 2);

-- =====================================================
-- Dữ liệu mẫu - Sản phẩm (Xiaomi)
-- =====================================================
INSERT INTO products (id, name, price, original_price, image, description, stock, category_id) VALUES
('xiaomi14', 'Xiaomi 14 Ultra 512GB', 22999000, 24999000, 'images/xiaomi14.jpg', 'Xiaomi 14 Ultra với chip Snapdragon 8 Gen 3, camera Leica 1 inch, màn hình LTPO AMOLED 2.8K, 120Hz', 8, 3),
('xiaomi14t', 'Xiaomi 14T Pro 512GB', 14999000, NULL, 'images/xiaomi14t.jpg', 'Xiaomi 14T Pro với chip Snapdragon 8s Gen 3, camera 50MP chính, màn hình LTPO AMOLED 2K, 144Hz', 35, 3),
('redmi14', 'Redmi 14C 128GB', 5999000, 6499000, 'images/redmi14.jpg', 'Redmi 14C với chip MediaTek Helio G99, camera 50MP chính, màn hình IPS LCD 90Hz, 4500mAh', 50, 3);

-- =====================================================
-- Dữ liệu mẫu - Sản phẩm (Vivo)
-- =====================================================
INSERT INTO products (id, name, price, original_price, image, description, stock, category_id) VALUES
('vivoX100', 'Vivo X100 512GB', 18999000, NULL, 'images/vivox100.jpg', 'Vivo X100 với chip Snapdragon 8 Gen 3, camera Zeiss 1 inch, màn hình LTPO AMOLED 2K, 120Hz', 12, 4),
('vivoX90', 'Vivo X90 Pro+ 512GB', 16999000, NULL, 'images/vivox90.jpg', 'Vivo X90 Pro+ với chip Snapdragon 8 Gen 2, camera Zeiss 1 inch, màn hình LTPO AMOLED 2K, 120Hz', 15, 4);

-- =====================================================
-- Dữ liệu mẫu - Sản phẩm (Oppo)
-- =====================================================
INSERT INTO products (id, name, price, original_price, image, description, stock, category_id) VALUES
('oppoFindX7', 'Oppo Find X7 Ultra 512GB', 21999000, NULL, 'images/oppofindx7.jpg', 'Oppo Find X7 Ultra với chip Snapdragon 8 Gen 3, camera Hasselblad 1 inch, màn hình LTPO AMOLED 2K, 120Hz', 10, 5),
('oppoFindX6', 'Oppo Find X6 512GB', 14999000, 15999000, 'images/oppofindx6.jpg', 'Oppo Find X6 với chip Snapdragon 8 Gen 2, camera Hasselblad 50MP, màn hình LTPO AMOLED 2K, 120Hz', 18, 5);

-- =====================================================
-- Dữ liệu mẫu - Sản phẩm (Nokia)
-- =====================================================
INSERT INTO products (id, name, price, original_price, image, description, stock, category_id) VALUES
('nokiaG420', 'Nokia G42 128GB', 4999000, NULL, 'images/nokiaG420.jpg', 'Nokia G42 với chip MediaTek Helio G88, camera 50MP chính, màn hình IPS LCD 90Hz, 5000mAh', 40, 6),
('nokia83', 'Nokia 8.3 5G 128GB', 4499000, 4799000, 'images/nokia83.jpg', 'Nokia 8.3 5G với chip Snapdragon 695, camera 64MP kép, màn hình AMOLED 2K, 120Hz, 5G', 22, 6);

-- =====================================================
-- Dữ liệu mẫu - Người dùng (Admin)
-- =====================================================
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- =====================================================
-- View - Tổng số sản phẩm theo danh mục
-- =====================================================
CREATE OR REPLACE VIEW IF NOT EXISTS v_categories_count AS
SELECT 
    c.id,
    c.name,
    c.description,
    COUNT(p.id) AS product_count
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id, c.name, c.description, c.image, c.display_order;
