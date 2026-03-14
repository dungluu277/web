<?php
$page_title = 'Lịch sử đơn hàng';
require_once 'functions.php';
requireLogin();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

include 'header.php';
?>

<h2 class="text-success mb-3"><i class="fas fa-history"></i> Lịch sử đơn hàng</h2>

<?php if (empty($orders)): ?>
<div class="alert alert-info">Bạn chưa có đơn hàng nào. <a href="index.php">Mua sắm ngay</a></div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-success">
            <tr>
                <th>Mã ĐH</th>
                <th>Ngày đặt</th>
                <th>Địa chỉ giao</th>
                <th>Thanh toán</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td><?= clean(mb_substr($o['delivery_address'], 0, 50)) ?>...</td>
                <td><?= getPaymentMethodText($o['payment_method']) ?></td>
                <td class="text-danger fw-bold"><?= formatPrice($o['total_amount']) ?></td>
                <td><?= getOrderStatusBadge($o['status']) ?></td>
                <td><a href="order-complete.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>