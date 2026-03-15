<?php
$page_title = 'Chi tiết đơn hàng';
require_once '../functions.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT o.*, u.fullname, u.username, u.email, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) { header('Location: orders.php'); exit; }

// Cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    $valid = ['pending', 'confirmed', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid)) {
        // Nếu hủy đơn, hoàn lại tồn kho
        if ($new_status === 'cancelled' && $order['status'] !== 'cancelled') {
            $stmt = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
            foreach ($items as $item) {
                $stmt2 = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmt2->execute([$item['quantity'], $item['product_id']]);
            }
        }
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        setFlash('success', 'Cập nhật trạng thái đơn hàng thành công');
        header("Location: order-detail.php?id=$id");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT od.*, p.name, p.code, p.image FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-file-invoice"></i> Đơn hàng #<?= $id ?></h2>
    <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Chi tiết sản phẩm -->
        <div class="card shadow-sm mb-3">
            <div class="card-header"><h5 class="mb-0">Sản phẩm đặt mua</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Ảnh</th><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                        <tr>
                            <td><img src="../<?= getProductImage($d['image'], $d['name']) ?>" width="50" class="rounded"></td>
                            <td><?= htmlspecialchars($d['name']) ?> <small class="text-muted">(<?= $d['code'] ?>)</small></td>
                            <td><?= formatPrice($d['unit_price']) ?></td>
                            <td><?= $d['quantity'] ?></td>
                            <td class="text-danger"><?= formatPrice($d['unit_price'] * $d['quantity']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-warning">
                            <td colspan="4" class="text-end fw-bold">Tổng cộng:</td>
                            <td class="text-danger fw-bold fs-5"><?= formatPrice($order['total_amount']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Thông tin đơn hàng -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white"><h5 class="mb-0">Thông tin đơn hàng</h5></div>
            <div class="card-body">
                <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['fullname']) ?> (<?= htmlspecialchars($order['username']) ?>)</p>
                <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['delivery_name']) ?></p>
                <p><strong>SĐT:</strong> <?= htmlspecialchars($order['delivery_phone']) ?></p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['delivery_address']) ?></p>
                <p><strong>Thanh toán:</strong> <?= getPaymentMethodText($order['payment_method']) ?></p>
                <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                <p><strong>Trạng thái:</strong> <?= getOrderStatusBadge($order['status']) ?></p>
                <?php if ($order['note']): ?>
                <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Cập nhật trạng thái -->
        <div class="card shadow-sm">
            <div class="card-header bg-warning"><h5 class="mb-0">Cập nhật trạng thái</h5></div>
            <div class="card-body">
                <form method="POST">
                    <select name="new_status" class="form-select mb-2">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Đã giao thành công</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>