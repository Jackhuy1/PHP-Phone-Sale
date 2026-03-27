<?php
/**
 * Thanh toán - Xử lý đơn hàng
 */
require_once 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// Kiểm tra xem có giỏ hàng chưa
$cartItemCount = 0;
if (!isset($_SESSION['cart_items'])) {
    $cartItemCount = 0;
} else {
    $cartItemCount = count($_SESSION['cart_items']);
}

// Nếu không có giỏ hàng
if ($cartItemCount === 0) {
    header("Location: cart.php");
    exit;
}

// Xử lý form thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    
    // Tính tổng tiền
    $totalAmount = 0;
    $orderItems = [];
    
    foreach ($_SESSION['cart_items'] as $productId => $item) {
        $productData = getDBConnection()->query("
            SELECT p.*, p.price, p.original_price
            FROM products p
            WHERE p.id = ?
        ", [$productId])->fetch();

        if ($productData) {
            $quantity = $item['quantity'] ?? 1;
            $price = $productData['original_price'] ?? $productData['price'];
            $totalAmount += $price * $quantity;
            $orderItems[] = [
                'product_id' => $productData['id'],
                'name' => $productData['name'],
                'price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
            ];
        }
    }

    // Tạo ID đơn hàng mới
    $orderId = generateId();

    // Chèn đơn hàng vào database
    $stmt = getDBConnection()->prepare("
        INSERT INTO orders (id, user_id, total_amount, status, address, phone, payment_method)
        VALUES (?, ?, ?, 'pending', ?, ?, ?)
    ");
    $stmt->execute([$orderId, $_SESSION['user_id'], $totalAmount, $address, $phone, $paymentMethod]);

    // Chèn chi tiết đơn hàng
    foreach ($orderItems as $item) {
        $stmt = getDBConnection()->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Xóa giỏ hàng
    unset($_SESSION['cart_items']);

    // Thông báo thành công
    header("Location: orders.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Shop Điện Thoại</title>
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
                        🛒 Giỏ hàng (<?= $cartItemCount ?>)
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="checkout-page">
        <div class="container">
            <div class="checkout-page-header">
                <a href="cart.php" class="back-btn">← Quay lại giỏ hàng</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    ✅ Đơn hàng đã được đặt thành công! Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.
                </div>
            <?php endif; ?>

            <div class="checkout-content">
                <div class="checkout-form">
                    <h2>Thông tin giao hàng</h2>
                    <form action="" method="POST" class="checkout-form-inner">
                        <div class="form-group">
                            <label for="address">Địa chỉ giao hàng:</label>
                            <textarea id="address" name="address" required placeholder="Nhập địa chỉ đầy đủ (hướng dẫn đường)..." maxlength="255"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại:</label>
                            <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}" placeholder="09xxxxxxxx" maxlength="11">
                        </div>
                        <div class="form-group">
                            <label>Phương thức thanh toán:</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <span>Tiền mặt</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <span>Chuyển khoản ngân hàng</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="payment_method" value="vnpay">
                                    <span>QR Code (MoMo, VNPay)</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">
                            💳 Đặt hàng ngay
                        </button>
                    </form>
                </div>

                <div class="checkout-summary">
                    <h3>Tổng kết đơn hàng</h3>
                    <div class="summary-row">
                        <span>Số lượng:</span>
                        <strong><?= count($_SESSION['cart_items']) ?> sản phẩm</strong>
                    </div>
                    <div class="summary-row">
                        <span>Thành tiền:</span>
                        <strong><?= number_format(array_sum(array_map(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1)), $_SESSION['cart_items'])) / 1000, 0, ',', '.') ?>T</strong>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <strong><?= number_format(array_sum(array_map(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1)), $_SESSION['cart_items'])) / 1000, 0, ',', '.') ?>T</strong>
                    </div>
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
