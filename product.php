<?php
/**
 * Chi tiết sản phẩm
 * Hiển thị thông tin chi tiết của một sản phẩm
 */
require_once 'config.php';

// Lấy ID sản phẩm từ URL
$productId = isset($_GET['id']) ? $_GET['id'] : null;

// Kiểm tra xem ID hợp lệ không
if (!$productId) {
    header("Location: index.php?redirect=1");
    exit;
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    $_SESSION['redirect'] = 'product.php?id=' . $productId;
    header("Location: login.php");
    exit;
}

try {
    // Lấy thông tin sản phẩm
    $stmt = getDBConnection()->prepare("
        SELECT p.*, c.name as category_name, c.description as category_description
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: index.php?redirect=1");
        exit;
    }

    // Lấy thông tin người dùng
    $stmt = getDBConnection()->query("SELECT username, email FROM users WHERE id = ?");
    $user = $stmt->fetch();

    // Kiểm tra xem sản phẩm đã trong giỏ hàng chưa
    $inCart = false;
    $cartQuantity = 1;
    $stmt = getDBConnection()->prepare("
        SELECT * FROM cart 
        WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        $inCart = true;
        $cartQuantity = $cartItem['quantity'];
    }

    // Tính tổng giỏ hàng
    $totalCartCount = 0;
    $stmt = getDBConnection()->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalCartCount = $stmt->fetch()['count'];

    // Kiểm tra xem có đơn hàng chưa
    $hasOrder = getDBConnection()->query("SELECT COUNT(*) as count FROM orders WHERE user_id = ?")->fetch()['count'] > 0;

    // Xử lý form thêm vào giỏ hàng
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            // Kiểm tra số lượng tồn kho
            if ($quantity > $product['stock']) {
                $error = 'Số lượng vượt quá số lượng tồn kho!';
            } else {
                // Thêm vào giỏ hàng
                $stmt = getDBConnection()->prepare("
                    INSERT INTO cart (user_id, product_id, quantity)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $productId, $quantity]);
                $success = 'Đã thêm sản phẩm vào giỏ hàng!';
                
                // Xóa biến redirect
                unset($_SESSION['redirect']);
            }
        } elseif ($_POST['action'] === 'update') {
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            if ($quantity > $product['stock']) {
                $error = 'Số lượng vượt quá số lượng tồn kho!';
            } else {
                $stmt = getDBConnection()->prepare("
                    UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?
                ");
                $stmt->execute([$quantity, $_SESSION['user_id'], $productId]);
                $success = 'Đã cập nhật số lượng!';
                
                unset($_SESSION['redirect']);
            }
        } elseif ($_POST['action'] === 'remove') {
            $stmt = getDBConnection()->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $productId]);
            $success = 'Đã xóa sản phẩm khỏi giỏ hàng!';
            
            unset($_SESSION['redirect']);
        }
    }
} catch (Exception $e) {
    $error = 'Lỗi: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Shop Điện Thoại</title>
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
                        <input type="hidden" name="category" value="<?= $product['category_id'] ?>">
                        <input type="text" name="search" placeholder="Tìm kiếm điện thoại...">
                        <button type="submit" class="search-btn">🔍</button>
                    </form>
                </div>
                <div class="cart-info">
                    <a href="cart.php" class="cart-link">
                        🛒 Giỏ hàng (<?= $totalCartCount ?>)
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="product-detail">
        <div class="container">
            <div class="product-detail-header">
                <a href="index.php" class="back-btn">← Quay lại danh sách</a>
            </div>

            <div class="product-detail-content">
                <div class="product-detail-image">
                    <img src="<?= $product['image'] ?: 'assets/images/default.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <div class="product-detail-info">
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

                    <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="product-category">
                        <a href="index.php?category=<?= $product['category_id'] ?>">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </a>
                    </div>

                    <div class="product-price-section">
                        <div class="current-price">
                            <?php
                            $price = $product['original_price'] ?? $product['price'];
                            $formatPrice = number_format($price / 1000, 0, ',', '.') . 'T';
                            echo $formatPrice;
                            ?>
                        </div>
                        <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                            <div class="original-price">
                                <?php
                                $originalPrice = $product['original_price'] / 1000;
                                $discount = ((($originalPrice - $price) / $originalPrice) * 100);
                                echo number_format($originalPrice, 0, ',', '.').'T ' . ($discount . '% giảm giá');
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <h3>Mô tả sản phẩm</h3>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                    </div>

                    <div class="product-stock">
                        <span class="stock-label">Số lượng tồn kho:</span>
                        <strong><?= $product['stock'] ?> sản phẩm</strong>
                    </div>

                    <form action="cart.php" method="POST" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="quantity-selector">
                            <label>Số lượng:</label>
                            <input type="number" name="quantity" value="<?= $cartQuantity ?>" min="1" max="<?= $product['stock'] ?>" required>
                        </div>
                        <button type="submit" name="action" value="add" class="btn btn-primary">
                            🛒 Thêm vào giỏ hàng
                        </button>
                    </form>

                    <?php if ($inCart): ?>
                        <form action="cart.php" method="POST" class="cart-actions">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="action" value="update">
                            <div class="quick-quantity">
                                <label>Số lượng:</label>
                                <input type="number" name="quantity" value="<?= $cartQuantity ?>" min="1" max="<?= $product['stock'] ?>">
                                <button type="submit" name="action" value="update" class="btn btn-secondary">Cập nhật</button>
                                <button type="submit" name="action" value="remove" class="btn btn-danger">Xóa</button>
                            </div>
                        </form>
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
