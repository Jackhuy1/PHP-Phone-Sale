<?php
/**
 * Xem đơn hàng - Hiển thị thông tin đơn hàng chi tiết
 */
require_once 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    header("Location: login.php?redirect=orders.php");
    exit;
}

// Lấy ID đơn hàng
$orderId = isset($_GET['id']) ? $_GET['id'] : null;

// Kiểm tra xem ID hợp lệ không
if (!$orderId) {
    header("Location: admin.php?redirect=1");
    exit;
}

// Lấy thông tin đơn hàng
$stmt = getDBConnection()->prepare("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: admin.php?redirect=1");
    exit;
}

// Lấy chi tiết đơn hàng
$stmt = getDBConnection()->prepare("
    SELECT oi.*, p.name, p.price, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Cập nhật tổng tiền
$orderId = $order['id'];
$stmt = getDBConnection()->prepare("
    SELECT SUM(total_amount) as sum FROM orders WHERE id = ?
");
$stmt->execute([$orderId]);
$totalAmount = $stmt->fetch()['sum'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng #<?= $orderId ?> - Shop Điện Thoại</title>
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
    <main class="orders-page">
        <div class="container">
            <div class="orders-page-header">
                <a href="admin.php" class="back-btn">← Quay lại</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    ✅ Đơn hàng đã được đặt thành công!
                </div>
            <?php endif; ?>

            <div class="orders-content">
                <div class="order-info">
                    <h2>Thông tin đơn hàng #<?= htmlspecialchars($orderId) ?></h2>
                    
                    <div class="info-row">
                        <span class="label">Khách hàng:</span>
                        <span class="value"><?= htmlspecialchars($order['username'] . ' - ' . $order['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Trạng thái:</span>
                        <span class="value status-<?= $order['status'] ?>">
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
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phương thức thanh toán:</span>
                        <span class="value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Địa chỉ giao hàng:</span>
                        <span class="value"><?= htmlspecialchars($order['address'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Số điện thoại:</span>
                        <span class="value"><?= htmlspecialchars($order['phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngày đặt hàng:</span>
                        <span class="value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tổng tiền:</span>
                        <span class="value"><?= number_format($totalAmount / 1000, 0, ',', '.') ?>T</span>
                    </div>
                </div>

                <div class="order-items">
                    <h3>Chi tiết đơn hàng</h3>
                    <?php if (!empty($items)): ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Hình</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td>
                                            <img src="<?= $item['image'] ?: 'assets/images/default.jpg' ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-thumb">
                                        </td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= number_format($item['price'] / 1000, 0, ',', '.') ?>T</td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= number_format(($item['price'] * $item['quantity']) / 1000, 0, ',', '.') ?>T</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Đơn hàng này không có sản phẩm nào.</p>
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
