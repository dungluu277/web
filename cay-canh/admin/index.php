<?php
$page_title = 'Dashboard';
require_once '../functions.php';

// Thống kê
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('confirmed','delivered')")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5 AND status='active'")->fetchColumn();

// Đơn hàng gần đây
$recent_orders = $pdo->query("SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();

include 'header.php';
?>

<h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card dashboard-card bg-primary text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Sản phẩm</h6>
                        <h2><?= $total_products ?></h2>
                    </div>
                    <i class="fas fa-seedling fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card bg-success text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Đơn hàng</h6>
                        <h2><?= $total_orders ?></h2>
                    </div>
                    <i class="fas fa-shopping-bag fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card bg-info text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Khách hàng</h6>
                        <h2><?= $total_customers ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card bg-warning text-dark shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Doanh thu</h6>
                        <h4><?= formatPrice($total_revenue) ?></h4>
                    </div>
                    <i class="fas fa-chart-line fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i> <strong><?= $pending_orders ?></strong> đơn hàng chờ xử lý
            <a href="orders.php?status=pending" class="float-end">Xem ngay</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <strong><?= $low_stock ?></strong> sản phẩm sắp hết hàng
            <a href="reports.php?tab=lowstock" class="float-end">Xem ngay</a>
        </div>
    </div>
</div>

<!-- Đơn hàng gần đây -->
<div class="card shadow-sm">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Đơn hàng gần đây</h5></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $o): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['fullname']) ?></td>
                    <td class="text-danger"><?= formatPrice($o['total_amount']) ?></td>
                    <td><?= getOrderStatusBadge($o['status']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>