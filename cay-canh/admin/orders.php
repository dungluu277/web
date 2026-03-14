<?php
$page_title = 'Quản lý đơn hàng';
require_once '../functions.php';

$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'date'; // date or address

$where = ["1=1"];
$params = [];

if ($status_filter) {
    $where[] = "o.status = ?";
    $params[] = $status_filter;
}
if ($date_from) {
    $where[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $where[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

$where_sql = implode(' AND ', $where);
$order_sql = $sort_by === 'address' ? 'o.delivery_address ASC' : 'o.created_at DESC';

$stmt = $pdo->prepare("SELECT o.*, u.fullname, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE {$where_sql} ORDER BY {$order_sql}");
$stmt->execute($params);
$orders = $stmt->fetchAll();

include 'header.php';
?>

<h2 class="mb-3"><i class="fas fa-shopping-bag"></i> Quản lý đơn hàng</h2>

<!-- Filter -->
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sắp xếp</label>
                <select name="sort_by" class="form-select">
                    <option value="date" <?= $sort_by === 'date' ? 'selected' : '' ?>>Theo ngày</option>
                    <option value="address" <?= $sort_by === 'address' ? 'selected' : '' ?>>Theo địa chỉ</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                <a href="orders.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th><th>Khách hàng</th><th>Địa chỉ giao</th><th>Thanh toán</th>
                    <th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th><th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($o['fullname']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($o['delivery_phone']) ?></small>
                    </td>
                    <td><small><?= htmlspecialchars(mb_substr($o['delivery_address'], 0, 40)) ?>...</small></td>
                    <td><?= getPaymentMethodText($o['payment_method']) ?></td>
                    <td class="text-danger fw-bold"><?= formatPrice($o['total_amount']) ?></td>
                    <td><?= getOrderStatusBadge($o['status']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td>
                        <a href="order-detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr><td colspan="8" class="text-center text-muted">Không có đơn hàng nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted mt-2">Tổng: <?= count($orders) ?> đơn hàng</p>

<?php include 'footer.php'; ?>