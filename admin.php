<?php
/**
 * Trang quản trị - Dashboard
 * Hiển thị thông tin quản trị và quản lý sản phẩm
 */
require_once 'config.php';

// Kiểm tra xem là admin không
if (!isAdmin()) {
    header("Location: login.php?redirect=admin.php");
    exit;
}

// Lấy thống kê
$totalProducts = getDBConnection()->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
$totalCategories = getDBConnection()->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
$totalOrders = getDBConnection()->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$totalRevenue = getDBConnection()->query("SELECT SUM(total_amount) as sum FROM orders")->fetch()['sum'] ?? 0;
$todayOrders = getDBConnection()->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")->fetch()['count'];

// Lấy danh sách sản phẩm
$products = getDBConnection()->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Lấy danh sách đơn hàng
$orders = getDBConnection()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Shop Điện Thoại</title>
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
    <main class="admin-dashboard">
        <div class="container">
            <div class="admin-dashboard-header">
                <h2>📊 Dashboard Quản trị</h2>
                <a href="add_product.php" class="btn btn-primary">
                    ➕ Thêm sản phẩm
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <h3><?= $totalProducts ?></h3>
                        <p>Tổng số sản phẩm</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <h3><?= $totalCategories ?></h3>
                        <p>Tổng số danh mục</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-content">
                        <h3><?= $totalOrders ?></h3>
                        <p>Tổng số đơn hàng</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3><?= number_format($totalRevenue / 1000, 0, ',', '.') ?>T</h3>
                        <p>Tổng doanh thu</p>
                    </div>
                </div>
            </div>

            <div class="admin-content">
                <div class="admin-section">
                    <div class="section-header">
                        <h3>📦 Sản phẩm gần đây</h3>
                        <a href="add_product.php" class="btn btn-small">Thêm mới</a>
                    </div>
                    <?php if (!empty($products)): ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Danh mục</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td>
                                            <img src="<?= $product['image'] ?: 'assets/images/default.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumb">
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= number_format($product['price'] / 1000, 0, ',', '.') ?>T</td>
                                        <td><?= $product['stock'] ?></td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($product['created_at'])) ?></td>
                                        <td>
                                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-small">Xem</a>
                                            <a href="add_product.php" class="btn btn-small btn-edit">Chỉnh sửa</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Chưa có sản phẩm nào.</p>
                    <?php endif; ?>
                </div>

                <div class="admin-section">
                    <div class="section-header">
                        <h3>📦 Đơn hàng gần đây</h3>
                    </div>
                    <?php if (!empty($orders)): ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= $order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['user_id'] . ' - Admin') ?></td>
                                        <td><?= number_format($order['total_amount'] / 1000, 0, ',', '.') ?>T</td>
                                        <td>
                                            <?php
                                            $status = $order['status'];
                                            $statusText = match($status) {
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'shipped' => 'Đang giao',
                                                'delivered' => 'Đã giao',
                                                'cancelled' => 'Đã hủy',
                                                default => $status
                                            };
                                            echo $statusText;
                                            ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-small">Xem</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Chưa có đơn hàng nào.</p>
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
