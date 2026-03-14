<?php
$page_title = 'Đặt hàng thành công';
require_once 'functions.php';
requireLogin();

$order_id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT od.*, p.name, p.code, p.image FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$details = $stmt->fetchAll();

include 'header.php';
?>

<div class="text-center mb-4">
    <i class="fas fa-check-circle text-success" style="font-size:80px;"></i>
    <h2 class="text-success mt-3">Đặt hàng thành công!</h2>
    <p class="text-muted">Mã đơn hàng: <strong>#<?= $order_id ?></strong></p>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Chi tiết đơn hàng #<?= $order_id ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Người nhận:</strong> <?= clean($order['delivery_name']) ?><br>
                        <strong>Điện thoại:</strong> <?= clean($order['delivery_phone']) ?><br>
                        <strong>Địa chỉ:</strong> <?= clean($order['delivery_address']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Thanh toán:</strong> <?= getPaymentMethodText($order['payment_method']) ?><br>
                        <strong>Trạng thái:</strong> <?= getOrderStatusBadge($order['status']) ?><br>
                        <strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                    </div>
                </div>

                <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                <div class="alert alert-info">
                    <strong>Thông tin chuyển khoản:</strong><br>
                    Ngân hàng: Vietcombank | STK: 1234567890<br>
                    Chủ TK: CÔNG TY CÂY CẢNH XANH<br>
                    Nội dung: DH<?= $order_id ?>_<?= $order['delivery_name'] ?>
                </div>
                <?php endif; ?>

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                        <tr>
                            <td><?= clean($d['name']) ?> (<?= clean($d['code']) ?>)</td>
                            <td><?= formatPrice($d['unit_price']) ?></td>
                            <td><?= $d['quantity'] ?></td>
                            <td class="text-danger"><?= formatPrice($d['unit_price'] * $d['quantity']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                            <td class="text-danger fw-bold"><?= formatPrice($order['total_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-success"><i class="fas fa-home"></i> Tiếp tục mua sắm</a>
            <a href="order-history.php" class="btn btn-outline-success"><i class="fas fa-list"></i> Xem lịch sử đơn hàng</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>