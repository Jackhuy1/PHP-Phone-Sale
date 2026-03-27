<?php
/**
 * Trang chủ - Hiển thị danh sách sản phẩm
 * File này hiển thị danh sách điện thoại theo danh mục
 */
require_once 'config.php';

// Lấy danh sách danh mục
$categories = getDBConnection()->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

// Lấy danh sách sản phẩm theo danh mục
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;

if ($categoryId > 0) {
    $products = getDBConnection()->query(
        "SELECT p.*, c.name as category_name 
         FROM products p 
         JOIN categories c ON p.category_id = c.id 
         WHERE p.category_id = ? 
         ORDER BY p.created_at DESC"
    , [$categoryId]
    )->fetchAll();
} else {
    // Lấy tất cả sản phẩm
    $products = getDBConnection()->query(
        "SELECT p.*, c.name as category_name 
         FROM products p 
         JOIN categories c ON p.category_id = c.id 
         ORDER BY p.created_at DESC"
    )->fetchAll();
}

// Số lượng sản phẩm
$productCount = count($products) ?: 0;

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    // Nếu chưa đăng nhập và có biến redirect
    if (isset($_GET['redirect'])) {
        $_SESSION['redirect'] = $_GET['redirect'];
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Điện Thoại - Mua Bán Điện Thoại Online</title>
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
                        <input type="hidden" name="category" value="<?= $categoryId ?>">
                        <input type="text" name="search" placeholder="Tìm kiếm điện thoại..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit" class="search-btn">🔍</button>
                    </form>
                </div>
                <div class="cart-info">
                    <?php
                    // Kiểm tra giỏ hàng
                    $cartItemCount = 0;
                    if (isLoggedIn()) {
                        $stmt = getDBConnection()->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $result = $stmt->fetch();
                        $cartItemCount = $result['count'];
                    }
                    ?>
                    <a href="cart.php" class="cart-link">
                        🛒 Giỏ hàng (<?= $cartItemCount ?>)
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Categories -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">📋 Danh mục sản phẩm</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="index.php?category=<?= $category['id'] ?>" class="category-card">
                        <div class="category-image">
                            <?= $category['image'] ?: '📱' ?>
                        </div>
                        <div class="category-info">
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                            <p><?= htmlspecialchars($category['description'] ?? '') ?></p>
                            <p class="product-count"><?= $category['product_count'] ?> sản phẩm</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="products-section">
        <div class="container">
            <div class="products-header">
                <h2 class="section-title">
                    <?= $categoryId > 0 ? "📱 Sản phẩm: " . $categories[$categoryId - 1]['name'] : "🔥 Sản phẩm nổi bật" ?>
                </h2>
                <?php if ($categoryId > 0): ?>
                    <a href="index.php" class="btn-back">← Xem tất cả</a>
                <?php endif; ?>
            </div>

            <?php if ($productCount > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product.php?id=<?= $product['id'] ?>">
                                    <img src="<?= $product['image'] ?: 'assets/images/default.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                </a>
                                <div class="product-actions">
                                    <form action="cart.php" method="POST" class="add-to-cart">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="add-cart-btn">🛒</button>
                                    </form>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="product.php?id=<?= $product['id'] ?>">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </h3>
                                <div class="product-price">
                                    <span class="current-price">
                                        <?php
                                        $price = $product['original_price'] ?? $product['price'];
                                        $formatPrice = number_format($price / 1000, 0, ',', '.') . 'T';
                                        echo $formatPrice;
                                        ?>
                                    </span>
                                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                        <span class="original-price">
                                            <?php
                                            $originalPrice = $product['original_price'] / 1000;
                                            echo number_format($originalPrice, 0, ',', '.').'T';
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-meta">
                                    <span class="stock"><?= $product['stock'] ?> tồn kho</span>
                                </div>
                                <p class="product-desc"><?= htmlspecialchars(mb_substr($product['description'], 0, 80)) ?>...</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <p>Không có sản phẩm nào!</p>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php
            $resultsPerPage = 9;
            $pagination = new Pagination($products, $resultsPerPage);
            $pagination->render();
            ?>
        </div>
    </section>

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
