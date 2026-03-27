<?php
/**
 * Giỏ hàng - Hiển thị và quản lý giỏ hàng
 */
require_once 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    header("Location: login.php?redirect=cart.php");
    exit;
}

// Lấy tất cả sản phẩm trong giỏ hàng
$cartItems = [];
$stmt = getDBConnection()->prepare("
    SELECT c.*, p.name, p.price, p.original_price, p.image, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cartRows = $stmt->fetchAll();

// Xử lý các thao tác trên giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'remove') {
            // Xóa sản phẩm khỏi giỏ hàng
            $stmt = getDBConnection()->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $_POST['product_id']]);
        } elseif ($_POST['action'] === 'update') {
            // Cập nhật số lượng
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $stmt = getDBConnection()->prepare("
                UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$quantity, $_SESSION['user_id'], $_POST['product_id']]);
        } elseif ($_POST['action'] === 'clear') {
            // Xóa toàn bộ giỏ hàng
            $stmt = getDBConnection()->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
    }
}

// Tính toán tổng giỏ hàng
$totalItems = 0;
$totalAmount = 0;
foreach ($cartRows as $item) {
    $totalItems += $item['quantity'];
    $itemPrice = $item['original_price'] ?? $item['price'];
    $totalAmount += $itemPrice * $item['quantity'];
}

// Kiểm tra xem có đơn hàng chưa
$hasOrder = getDBConnection()->query("SELECT COUNT(*) as count FROM orders WHERE user_id = ?")->fetch()['count'] > 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Shop Điện Thoại</title>
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
                        🛒 Giỏ hàng (<?= $totalItems ?>)
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="cart-page">
        <div class="container">
            <div class="cart-page-header">
                <a href="index.php" class="back-btn">← Quay lại</a>
            </div>

            <?php if (empty($cartRows)): ?>
                <div class="cart-empty">
                    <div class="empty-icon">🛒</div>
                    <h2>Giỏ hàng của bạn đang trống</h2>
                    <p>Chưa có sản phẩm nào trong giỏ hàng. Hãy thêm sản phẩm bạn yêu thích!</p>
                    <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartRows as $item): ?>
                                    <tr class="cart-item-row">
                                        <td class="product-cell">
                                            <div class="product-info">
                                                <a href="product.php?id=<?= $item['product_id'] ?>">
                                                    <img src="<?= $item['image'] ?: 'assets/images/default.jpg' ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                                </a>
                                                <div class="product-name">
                                                    <a href="product.php?id=<?= $item['product_id'] ?>">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="price-cell">
                                            <?php
                                            $price = $item['original_price'] ?? $item['price'];
                                            $formatPrice = number_format($price / 1000, 0, ',', '.') . 'T';
                                            echo $formatPrice;
                                            ?>
                                        </td>
                                        <td class="quantity-cell">
                                            <form action="cart.php" method="POST" class="quantity-form">
                                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                <input type="hidden" name="action" value="update">
                                                <div class="quantity-input">
                                                    <button type="submit" class="qty-btn minus">−</button>
                                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                                                    <button type="submit" class="qty-btn plus">+</button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="total-cell">
                                            <?php
                                            $itemTotal = ($item['original_price'] ?? $item['price']) * $item['quantity'];
                                            $formatTotal = number_format($itemTotal / 1000, 0, ',', '.') . 'T';
                                            echo $formatTotal;
                                            ?>
                                        </td>
                                        <td class="actions-cell">
                                            <form action="cart.php" method="POST" class="remove-item">
                                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="btn-remove" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-summary">
                        <h3>Tổng kết đơn hàng</h3>
                        <div class="summary-row">
                            <span>Số lượng:</span>
                            <strong><?= $totalItems ?> sản phẩm</strong>
                        </div>
                        <div class="summary-row">
                            <span>Thành tiền:</span>
                            <strong><?= number_format($totalAmount / 1000, 0, ',', '.') ?>T</strong>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>Miễn phí</span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <strong><?= number_format($totalAmount / 1000, 0, ',', '.') ?>T</strong>
                        </div>

                        <?php if ($hasOrder): ?>
                            <div class="existing-orders">
                                <h4>Đơn hàng trước đây</h4>
                                <a href="orders.php" class="btn btn-secondary">Xem đơn hàng</a>
                            </div>
                        <?php endif; ?>

                        <a href="checkout.php" class="btn btn-primary btn-full">
                            🛒 Thanh toán ngay
                        </a>

                        <form action="cart.php" method="POST">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-danger">Xóa toàn bộ giỏ hàng</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
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
