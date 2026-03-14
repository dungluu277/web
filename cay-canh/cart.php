<?php
$page_title = 'Giỏ hàng';
require_once 'functions.php';
requireLogin();

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $cart_id => $qty) {
            $qty = max(1, intval($qty));
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$qty, $cart_id, $_SESSION['user_id']]);
        }
        setFlash('success', 'Đã cập nhật giỏ hàng');
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
        setFlash('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
        header('Location: cart.php');
        exit;
    }
}

$items = getCartItems($pdo);
$total = 0;

include 'header.php';
?>

<h2 class="text-success mb-3"><i class="fas fa-shopping-cart"></i> Giỏ hàng</h2>

<?php if (empty($items)): ?>
<div class="alert alert-info">
    Giỏ hàng trống. <a href="index.php">Tiếp tục mua sắm</a>
</div>
<?php else: ?>
<form method="POST">
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-success">
                <tr>
                    <th>Hình ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th width="120">Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <?php 
                    $price = getSellingPrice($item['import_price'], $item['profit_margin']);
                    $subtotal = $price * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td width="80">
                        <img src="<?= getProductImage($item['image']) ?>" width="60" height="60" class="rounded" style="object-fit:cover;">
                    </td>
                    <td>
                        <a href="product-detail.php?id=<?= $item['product_id'] ?>" class="text-decoration-none"><?= clean($item['name']) ?></a>
                        <br><small class="text-muted">Mã: <?= clean($item['code']) ?></small>
                    </td>
                    <td class="text-danger"><?= formatPrice($price) ?></td>
                    <td>
                        <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control form-control-sm">
                    </td>
                    <td class="text-danger fw-bold"><?= formatPrice($subtotal) ?></td>
                    <td>
                        <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('remove_id').value=<?= $item['id'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-warning">
                    <td colspan="4" class="text-end fw-bold fs-5">Tổng cộng:</td>
                    <td colspan="2" class="text-danger fw-bold fs-5"><?= formatPrice($total) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <input type="hidden" name="cart_id" id="remove_id" value="">
    <div class="d-flex justify-content-between">
        <button type="submit" name="update_cart" class="btn btn-outline-success"><i class="fas fa-sync"></i> Cập nhật giỏ</button>
        <a href="checkout.php" class="btn btn-success btn-lg"><i class="fas fa-credit-card"></i> Tiến hành đặt hàng</a>
    </div>
</form>
<?php endif; ?>

<?php include 'footer.php'; ?>