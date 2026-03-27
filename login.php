<?php
/**
 * Đăng nhập - Xử lý đăng nhập
 * Kiểm tra thông tin đăng nhập và tạo session
 */
require_once 'config.php';

// Kiểm tra xem đã đăng nhập chưa
if (isLoggedIn()) {
    // Nếu đã đăng nhập và có biến redirect
    if (isset($_SESSION['redirect'])) {
        header("Location: " . $_SESSION['redirect']);
        unset($_SESSION['redirect']);
    } else {
        header("Location: index.php");
    }
    exit;
}

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        // Kiểm tra thông tin đăng nhập
        $stmt = getDBConnection()->prepare("
            SELECT id, username, email, password, role 
            FROM users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Tạo session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect
            header("Location: index.php");
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Shop Điện Thoại</title>
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
    <main class="login-page">
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <h2>Đăng nhập</h2>
                    <p>Vui lòng đăng nhập để tiếp tục</p>
                </div>

                <?php if (isset($_SESSION['redirect'])): ?>
                    <div class="alert alert-info">
                        Bạn sẽ được chuyển đến: <a href="<?= $_SESSION['redirect'] ?>"><?= $_SESSION['redirect'] ?></a>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập / Email:</label>
                        <div class="input-group">
                            <input type="text" id="username" name="username" required autofocus placeholder="Nhập tên đăng nhập hoặc email">
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu">
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                        </div>
                    </div>

                    <div class="login-footer">
                        <p>
                            <a href="#">Quên mật khẩu?</a> | 
                            <a href="#">Đăng ký mới</a>
                        </p>
                    </div>
                </form>
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
