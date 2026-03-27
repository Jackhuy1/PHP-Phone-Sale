<?php
/**
 * Trang quản trị - Thêm sản phẩm
 * Admin có thể thêm sản phẩm mới vào hệ thống
 */
require_once 'config.php';

// Kiểm tra xem là admin không
if (!isAdmin()) {
    header("Location: login.php?redirect=admin.php");
    exit;
}

// Xử lý form thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $originalPrice = trim($_POST['original_price'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? '');

    // Validation
    if (!$name || !$price || !$image || !$description || !$stock || !$categoryId) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        // Xử lý giá
        $price = floatval($price);
        if ($originalPrice) {
            $originalPrice = floatval($originalPrice);
        }
        $stock = intval($stock);

        // Chèn sản phẩm vào database
        try {
            $stmt = getDBConnection()->prepare("
                INSERT INTO products (id, name, price, original_price, image, description, stock, category_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                generateId(),
                $name,
                $price,
                $originalPrice,
                $image,
                $description,
                $stock,
                $categoryId
            ]);
            $success = 'Đã thêm sản phẩm thành công!';
        } catch (Exception $e) {
            $error = 'Lỗi khi thêm sản phẩm: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách danh mục
$categories = getDBConnection()->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

// Lấy danh sách sản phẩm hiện tại
$products = getDBConnection()->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Thêm sản phẩm - Shop Điện Thoại</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <a href="index.php">📱 Điện Thoại</a>
                </div>
                <nav class="header-nav">
                    <a href="index.php">Trang chủ</a>
                    <a href="admin.php">Quản trị</a>
                </nav>
            </div>
            <div class="header-main">
                <h1 class="title">📱 Shop Điện Thoại</h1>
                <p class="subtitle">Mua bán điện thoại chính hãng - Giá tốt nhất thị trường</p>
            </div>
            <div class="header-bottom">
                <div class="search-box">
                    <form action="index.php" method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Tìm kiếm điện thoại...">
                        <button type="submit" class="search-btn">🔍</button>
                    </form>
                </div>
                <div class="cart-info">
                    <a href="cart.php" class="cart-link">
                        🛒 Giỏ hàng
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-page">
        <div class="container">
            <div class="admin-page-header">
                <h2>📦 Quản trị sản phẩm</h2>
                <a href="admin.php" class="btn btn-secondary">← Quay lại</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="admin-content">
                <div class="admin-form">
                    <h3>Thêm sản phẩm mới</h3>
                    <form action="" method="POST" enctype="multipart/form-data" class="admin-form-inner">
                        <div class="form-group">
                            <label for="name">Tên sản phẩm:</label>
                            <input type="text" id="name" name="name" required placeholder="Nhập tên sản phẩm">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Giá (VNĐ):</label>
                                <input type="number" id="price" name="price" required placeholder="0" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="original_price">Giá gốc (VNĐ):</label>
                                <input type="number" id="original_price" name="original_price" placeholder="0" step="0.01">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image">URL hình ảnh:</label>
                            <input type="text" id="image" name="image" required placeholder="https://example.com/image.jpg">
                            <small class="form-hint">Nhập đường dẫn hình ảnh hoặc để trống để dùng ảnh mặc định</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả:</label>
                            <textarea id="description" name="description" rows="4" required placeholder="Nhập mô tả chi tiết sản phẩm"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stock">Số lượng tồn kho:</label>
                                <input type="number" id="stock" name="stock" required placeholder="0">
                            </div>
                            <div class="form-group">
                                <label for="category_id">Danh mục:</label>
                                <select id="category_id" name="category_id" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            💾 Thêm sản phẩm
                        </button>
                    </form>
                </div>

                <div class="admin-products-preview">
                    <h3>Danh sách sản phẩm gần đây</h3>
                    <?php if (!empty($products)): ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= number_format($product['price'] / 1000, 0, ',', '.') ?>T</td>
                                        <td><?= $product['stock'] ?></td>
                                        <td>
                                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-small">Xem</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Chưa có sản phẩm nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Về chúng tôi</h3>
                    <p>Shop điện thoại chính hãng - Cam kết chất lượng</p>
                </div>
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>📞 1900 xxxx</p>
                    <p>📧 contact@shop.com</p>
                </div>
                <div class="footer-section">
                    <h3>Thông tin</h3>
                    <p>Chính sách đổi trả</p>
                    <p>Hướng dẫn thanh toán</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Shop Điện Thoại. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
