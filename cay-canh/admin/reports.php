<?php
$page_title = 'Báo cáo thống kê';
require_once '../functions.php';

$tab = $_GET['tab'] ?? 'stock';

include 'header.php';
?>

<h2 class="mb-3"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</h2>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= $tab === 'stock' ? 'active' : '' ?>" href="reports.php?tab=stock">Tồn kho</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab === 'import_export' ? 'active' : '' ?>" href="reports.php?tab=import_export">Nhập - Xuất</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab === 'lowstock' ? 'active' : '' ?>" href="reports.php?tab=lowstock">Sắp hết hàng</a></li>
</ul>

<?php if ($tab === 'stock'): ?>
<!-- TỒN KHO THEO DANH MỤC -->
<?php
$cat_filter = intval($_GET['category_id'] ?? 0);
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$where = "1=1";
$params = [];
if ($cat_filter) {
    $where = "p.category_id = ?";
    $params = [$cat_filter];
}
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE $where ORDER BY c.name, p.name");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<div class="card shadow-sm">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="tab" value="stock">
            <select name="category_id" class="form-select" style="width:300px;">
                <option value="0">-- Tất cả danh mục --</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tra cứu</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark">
                <tr><th>Mã SP</th><th>Tên SP</th><th>Danh mục</th><th>Tồn kho</th><th>Đơn vị</th><th>Giá nhập TB</th><th>Giá trị tồn</th></tr>
            </thead>
            <tbody>
                <?php $total_value = 0; foreach ($products as $p): $val = $p['stock'] * $p['import_price']; $total_value += $val; ?>
                <tr>
                    <td><code><?= $p['code'] ?></code></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td><span class="badge bg-<?= $p['stock'] <= 5 ? 'danger' : 'success' ?>"><?= $p['stock'] ?></span></td>
                    <td><?= $p['unit'] ?></td>
                    <td><?= formatPrice($p['import_price']) ?></td>
                    <td><?= formatPrice($val) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="table-warning fw-bold">
                    <td colspan="6" class="text-end">Tổng giá trị tồn kho:</td>
                    <td><?= formatPrice($total_value) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'import_export'): ?>
<!-- BÁO CÁO NHẬP XUẤT -->
<?php
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT p.id, p.code, p.name, p.unit,
        COALESCE(imp.total_import, 0) as total_import,
        COALESCE(exp.total_export, 0) as total_export
    FROM products p
    LEFT JOIN (
        SELECT ird.product_id, SUM(ird.quantity) as total_import
        FROM import_receipt_details ird
        JOIN import_receipts ir ON ird.receipt_id = ir.id
        WHERE ir.status = 'completed' AND ir.import_date BETWEEN ? AND ?
        GROUP BY ird.product_id
    ) imp ON p.id = imp.product_id
    LEFT JOIN (
        SELECT od.product_id, SUM(od.quantity) as total_export
        FROM order_details od
        JOIN orders o ON od.order_id = o.id
        WHERE o.status IN ('confirmed','delivered') AND DATE(o.created_at) BETWEEN ? AND ?
        GROUP BY od.product_id
    ) exp ON p.id = exp.product_id
    WHERE COALESCE(imp.total_import, 0) > 0 OR COALESCE(exp.total_export, 0) > 0
    ORDER BY p.name
");
$stmt->execute([$date_from, $date_to, $date_from, $date_to]);
$report = $stmt->fetchAll();
?>
<div class="card shadow-sm">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <input type="hidden" name="tab" value="import_export">
            <div>
                <label class="form-label mb-0">Từ ngày</label>
                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
            </div>
            <div>
                <label class="form-label mb-0">Đến ngày</label>
                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark">
                <tr><th>Mã SP</th><th>Tên SP</th><th>Đơn vị</th><th class="text-success">Tổng nhập</th><th class="text-danger">Tổng xuất</th><th>Chênh lệch</th></tr>
            </thead>
            <tbody>
                <?php foreach ($report as $r): ?>
                <tr>
                    <td><code><?= $r['code'] ?></code></td>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><?= $r['unit'] ?></td>
                    <td class="text-success fw-bold">+<?= $r['total_import'] ?></td>
                    <td class="text-danger fw-bold">-<?= $r['total_export'] ?></td>
                    <td><span class="badge bg-<?= ($r['total_import'] - $r['total_export']) >= 0 ? 'success' : 'danger' ?>"><?= $r['total_import'] - $r['total_export'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($report)): ?>
                <tr><td colspan="6" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'lowstock'): ?>
<!-- CẢNH BÁO SẮP HẾT HÀNG -->
<?php
$threshold = intval($_GET['threshold'] ?? 10);
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock <= ? AND p.status = 'active' ORDER BY p.stock ASC");
$stmt->execute([$threshold]);
$low_products = $stmt->fetchAll();
?>
<div class="card shadow-sm">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <input type="hidden" name="tab" value="lowstock">
            <div>
                <label class="form-label mb-0">Số lượng tồn ≤</label>
                <input type="number" name="threshold" class="form-control" value="<?= $threshold ?>" min="1" style="width:100px;">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark">
                <tr><th>Mã SP</th><th>Tên SP</th><th>Danh mục</th><th>Tồn kho</th><th>Trạng thái</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($low_products as $p): ?>
                <tr class="<?= $p['stock'] == 0 ? 'table-danger' : 'table-warning' ?>">
                    <td><code><?= $p['code'] ?></code></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td>
                        <span class="badge bg-<?= $p['stock'] == 0 ? 'danger' : 'warning' ?> fs-6"><?= $p['stock'] ?></span>
                    </td>
                    <td><?= $p['stock'] == 0 ? '<span class="text-danger fw-bold">HẾT HÀNG</span>' : '<span class="text-warning fw-bold">SẮP HẾT</span>' ?></td>
                    <td><a href="import-form.php" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Nhập hàng</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($low_products)): ?>
                <tr><td colspan="6" class="text-center text-success"><i class="fas fa-check-circle"></i> Tất cả sản phẩm đều có đủ hàng</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>