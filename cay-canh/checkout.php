<?php
$page_title = 'Đặt hàng';
require_once 'functions.php';
requireLogin();

$user = getCurrentUser($pdo);
$items = getCartItems($pdo);

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addr_type = $_POST['address_type'] ?? 'account';
    $delivery_name = trim($_POST['delivery_name'] ?? '');
    $delivery_phone = trim($_POST['delivery_phone'] ?? '');
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $note = trim($_POST['note'] ?? '');
    
    // Validate
    if (empty($delivery_name)) $errors[] = 'Tên người nhận không được trống';
    if (empty($delivery_phone)) $errors[] = 'Số điện thoại không được trống';
    if (!preg_match('/^[0-9]{10,11}$/', $delivery_phone)) $errors[] = 'Số điện thoại không hợp lệ';
    if (empty($delivery_address)) $errors[] = 'Địa chỉ giao hàng không được trống';
    if (!in_array($payment_method, ['cash', 'bank_transfer', 'online'])) $errors[] = 'Phương thức thanh toán không hợp lệ';
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Tính tổng
            $total = 0;
            $order_items = [];
            foreach ($items as $item) {
                $price = getSellingPrice($item['import_price'], $item['profit_margin']);
                
                // Kiểm tra tồn kho
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch();
                
                if ($product['stock'] < $item['quantity']) {
                    throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng tồn kho");
                }
                
                $subtotal = $price * $item['quantity'];
                $total += $subtotal;
                $order_items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price
                ];
            }
            
            // Tạo đơn hàng
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, delivery_name, delivery_phone, delivery_address, payment_method, total_amount, status, note) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $delivery_name, $delivery_phone, $delivery_address, $payment_method, $total, $note]);
            $order_id = $pdo->lastInsertId();
            
            // Tạo chi tiết đơn hàng & trừ kho
            foreach ($order_items as $oi) {
                $stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $oi['product_id'], $oi['quantity'], $oi['unit_price']]);
                
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$oi['quantity'], $oi['product_id']]);
            }
            
            // Xóa giỏ hàng
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $pdo->commit();
            
            header('Location: order-complete.php?id=' . $order_id);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

// Tính tổng hiển thị
$total = 0;
foreach ($items as $item) {
    $total += getSellingPrice($item['import_price'], $item['profit_margin']) * $item['quantity'];
}

include 'header.php';
?>

<h2 class="text-success mb-3"><i class="fas fa-credit-card"></i> Đặt hàng</h2>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" id="checkoutForm">
<div class="row">
    <div class="col-md-7">
        <!-- Thông tin giao hàng -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Thông tin giao hàng</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Chọn địa chỉ:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="address_type" value="account" id="addrAccount" checked onchange="fillAccountAddress()">
                        <label class="form-check-label" for="addrAccount">Sử dụng địa chỉ tài khoản</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="address_type" value="new" id="addrNew" onchange="clearAddress()">
                        <label class="form-check-label" for="addrNew">Nhập địa chỉ mới</label>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" name="delivery_name" id="delivery_name" class="form-control" value="<?= clean($user['fullname']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" name="delivery_phone" id="delivery_phone" class="form-control" value="<?= clean($user['phone']) ?>" required pattern="[0-9]{10,11}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                        <textarea name="delivery_address" id="delivery_address" class="form-control" rows="2" required><?= clean($user['address'] . ', ' . $user['ward'] . ', ' . $user['district'] . ', ' . $user['city']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Phương thức thanh toán</h5></div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" value="cash" id="payCash" checked>
                    <label class="form-check-label" for="payCash"><i class="fas fa-money-bill-wave text-success"></i> Thanh toán tiền mặt khi nhận hàng (COD)</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" id="payBank">
                    <label class="form-check-label" for="payBank"><i class="fas fa-university text-primary"></i> Chuyển khoản ngân hàng</label>
                </div>
                <div id="bankInfo" class="alert alert-info d-none mt-2">
                    <strong>Thông tin chuyển khoản:</strong><br>
                    Ngân hàng: Vietcombank<br>
                    Số TK: 1234567890<br>
                    Chủ TK: CÔNG TY CÂY CẢNH XANH<br>
                    Nội dung CK: DH_[Họ tên]_[SĐT]
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" value="online" id="payOnline">
                    <label class="form-check-label" for="payOnline"><i class="fas fa-globe text-info"></i> Thanh toán trực tuyến</label>
                </div>
                <div id="onlineInfo" class="alert alert-warning d-none mt-2">
                    Chức năng thanh toán trực tuyến đang được phát triển. Đơn hàng sẽ được xử lý sau.
                </div>
                <div class="mt-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú cho đơn hàng..."></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <!-- Tóm tắt đơn hàng -->
        <div class="card shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-warning"><h5 class="mb-0">Tóm tắt đơn hàng</h5></div>
            <div class="card-body">
                <?php foreach ($items as $item): ?>
                <?php $price = getSellingPrice($item['import_price'], $item['profit_margin']); ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?= clean($item['name']) ?> x<?= $item['quantity'] ?></span>
                    <span class="text-danger"><?= formatPrice($price * $item['quantity']) ?></span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Tổng cộng:</span>
                    <span class="text-danger"><?= formatPrice($total) ?></span>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100 mt-3">
                    <i class="fas fa-check-circle"></i> Xác nhận đặt hàng
                </button>
                <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                </a>
            </div>
        </div>
    </div>
</div>
</form>

<script>
// Hiện thông tin thanh toán
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('bankInfo').classList.toggle('d-none', this.value !== 'bank_transfer');
        document.getElementById('onlineInfo').classList.toggle('d-none', this.value !== 'online');
    });
});

function fillAccountAddress() {
    document.getElementById('delivery_name').value = '<?= addslashes($user['fullname']) ?>';
    document.getElementById('delivery_phone').value = '<?= addslashes($user['phone']) ?>';
    document.getElementById('delivery_address').value = '<?= addslashes($user['address'] . ', ' . $user['ward'] . ', ' . $user['district'] . ', ' . $user['city']) ?>';
}

function clearAddress() {
    document.getElementById('delivery_name').value = '';
    document.getElementById('delivery_phone').value = '';
    document.getElementById('delivery_address').value = '';
}
</script>

<?php include 'footer.php'; ?>