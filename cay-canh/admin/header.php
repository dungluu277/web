<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../functions.php';
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Lấy thông tin đầy đủ của admin
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin_info = $stmt->fetch();

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="index.php"><i class="fas fa-leaf text-success"></i> <?= SITE_NAME ?> - Admin</a>
        <div class="d-flex align-items-center text-white">
            <button class="btn btn-link text-white me-3" style="text-decoration:none;" data-bs-toggle="modal" data-bs-target="#adminInfoModal" title="Xem thông tin quản trị viên">
                <i class="fas fa-user"></i> <span style="cursor:pointer;">Quản trị viên</span>
            </button>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 admin-sidebar">
                <nav class="nav flex-column py-3">
                    <div class="sidebar-heading">Tổng quan</div>
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <div class="sidebar-heading">Sản phẩm</div>
                    <a class="nav-link <?= $current_page == 'categories.php' ? 'active' : '' ?>" href="categories.php">
                        <i class="fas fa-tags"></i> Danh mục
                    </a>
                    <a class="nav-link <?= in_array($current_page, ['products.php','product-form.php']) ? 'active' : '' ?>" href="products.php">
                        <i class="fas fa-seedling"></i> Sản phẩm
                    </a>
                    
                    <div class="sidebar-heading">Kho hàng</div>
                    <a class="nav-link <?= in_array($current_page, ['imports.php','import-form.php']) ? 'active' : '' ?>" href="imports.php">
                        <i class="fas fa-truck-loading"></i> Nhập hàng
                    </a>
                    <a class="nav-link <?= $current_page == 'prices.php' ? 'active' : '' ?>" href="prices.php">
                        <i class="fas fa-dollar-sign"></i> Giá bán
                    </a>
                    
                    <div class="sidebar-heading">Đơn hàng</div>
                    <a class="nav-link <?= in_array($current_page, ['orders.php','order-detail.php']) ? 'active' : '' ?>" href="orders.php">
                        <i class="fas fa-shopping-bag"></i> Đơn hàng
                    </a>
                    
                    <div class="sidebar-heading">Hệ thống</div>
                    <a class="nav-link <?= in_array($current_page, ['users.php','user-form.php']) ? 'active' : '' ?>" href="users.php">
                        <i class="fas fa-users"></i> Người dùng
                    </a>
                    <a class="nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                        <i class="fas fa-chart-bar"></i> Báo cáo
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <?php
                // Flash messages
                if (isset($_SESSION['flash'])) {
                    $flash = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                    echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show'>
                            {$flash['message']}
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                          </div>";
                }
                ?>