<?php
$page_title = 'Quản lý giá bán';
require_once '../functions.php';

// Cập nhật tỉ lệ lợi nhuận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_margin'])) {
    $product_id = intval($_POST['product_id']);
    $margin = floatval($_POST['profit_margin']);
    if ($margin >= 0) {
        $stmt = $pdo->prepare("UPDATE products SET profit_margin = ? WHERE id = ?");
        $stmt->execute([$margin, $product_id]);
        setFlash('success', 'Cập nhật tỉ lệ lợi nhuận thành công');
        header('Location: prices.php');
        exit;
    }
}

// Danh sách sản phẩm
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.name")->fetchAll();

// Tra cứu theo lô hàng
$selected_product = intval($_GET['product_id'] ?? 0);
$import_batches = [];
if ($selected_product > 0) {
    $import_batches = $pdo->prepare("
        SELECT ird.*, ir.import_date, ir.id as receipt_id, p.profit_margin
        FROM import_receipt_details ird 
        JOIN import_receipts ir ON ird.receipt_id = ir.id 
        JOIN products p ON ird.product_id = p.id
        WHERE ird.product_id = ? AND ir.status = 'completed'
        ORDER BY ir.import_date DESC
    ");
    $import_batches->execute([$selected_product]);
    $import_batches = $import_batches->fetchAll();
}

include 'header.php';
?>

<h2 class="mb-3"><i class="fas fa-dollar-sign"></i> Quản lý giá bán</h2>

<!-- Bảng tỉ lệ lợi nhuận -->
<div class="card shadow-sm mb-4">
    <div class="card-header"><h5 class="mb-0">Tỉ lệ lợi nhuận theo sản phẩm</h5></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Mã SP</th><th>Tên SP</th><th>Danh mục</th><th>Giá nhập TB</th><th>% Lợi nhuận</th><th>Giá bán</th><th>Cập nhật</th></tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><code><?= htmlspecialchars($p['code']) ?></code></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td><?= formatPrice($p['import_price']) ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="number" name="profit_margin" value="<?= $p['profit_margin'] ?>" step="0.01" min="0" class="form-control form-control-sm" style="width:80px;">
                            <span class="align-self-center">%</span>
                            <button type="submit" name="update_margin" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td class="text-danger fw-bold"><?= formatPrice(getSellingPrice($p['import_price'], $p['profit_margin'])) ?></td>
                    <td><a href="prices.php?product_id=<?= $p['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-history"></i> Lô hàng</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tra cứu theo lô hàng -->
<?php if ($selected_product > 0): ?>
<?php
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$selected_product]);
    $sp = $stmt->fetch();
?>
<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Lịch sử nhập hàng: <?= htmlspecialchars($sp['name']) ?> (<?= htmlspecialchars($sp['code']) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>Phiếu nhập</th><th>Ngày nhập</th><th>SL nhập</th><th>Giá nhập</th><th>% LN</th><th>Giá bán (tại thời điểm)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($import_batches as $b): ?>
                <tr>
                    <td>#<?= $b['receipt_id'] ?></td>
                    <td><?= date('d/m/Y', strtotime($b['import_date'])) ?></td>
                    <td><?= $b['quantity'] ?></td>
                    <td><?= formatPrice($b['import_price']) ?></td>
                    <td><?= $b['profit_margin'] ?>%</td>
                    <td class="text-danger"><?= formatPrice(getSellingPrice($b['import_price'], $b['profit_margin'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($import_batches)): ?>
                <tr><td colspan="6" class="text-center text-muted">Chưa có lô hàng nhập</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>