<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/functions.php';
}
$_cart_count = getCartCount($pdo);
$_categories_nav = $pdo->query("SELECT * FROM categories ORDER BY CASE WHEN name = 'Cây nội thất' THEN 0 WHEN name = 'Cây ngoài thất' THEN 1 WHEN name = 'Cây bonsai' THEN 2 WHEN name = 'Cây phong thủy' THEN 3 WHEN name = 'Sen đá & Xương rồng' THEN 4 WHEN name = 'Phụ kiện chậu cây' THEN 5 ELSE 6 END")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Bar -->
    <div class="bg-success text-white py-1">
        <div class="container d-flex justify-content-between align-items-center">
            <small><i class="fas fa-phone"></i> Hotline: 0901 234 567</small>
            <div>
                <?php if (isLoggedIn()): ?>
                    <small>
                        <i class="fas fa-user"></i> Xin chào, <strong><?= clean($_SESSION['user_fullname']) ?></strong>
                        | <a href="profile.php" class="text-white">Thông tin cá nhân</a>
                        | <a href="order-history.php" class="text-white">Đơn hàng</a>
                        | <a href="logout.php" class="text-white">Đăng xuất</a>
                    </small>
                <?php else: ?>
                    <small>
                        <a href="login.php" class="text-white">Đăng nhập</a> | 
                        <a href="register.php" class="text-white">Đăng ký</a>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-leaf text-success"></i> <?= SITE_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Danh mục</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($_categories_nav as $cat): ?>
                            <li><a class="dropdown-item" href="category.php?id=<?= $cat['id'] ?>"><?= clean($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="search.php">Tìm kiếm</a></li>
                </ul>
                <!-- Search form -->
                <form class="d-flex me-3" action="search.php" method="GET">
                    <input class="form-control form-control-sm me-1" type="search" name="keyword" placeholder="Tìm cây cảnh..." value="<?= isset($_GET['keyword']) ? clean($_GET['keyword']) : '' ?>">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <!-- Cart -->
                <a href="cart.php" class="btn btn-outline-light position-relative">
                    <i class="fas fa-shopping-cart"></i> Giỏ hàng
                    <?php if ($_cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $_cart_count ?>
                    </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            <?php renderFlash(); ?>

