<?php
$page_title = 'Quản lý sản phẩm';
require_once '../functions.php';

$search = trim($_GET['search'] ?? '');
$cat_filter = intval($_GET['category_id'] ?? 0);
$page = intval($_GET['page'] ?? 1);

$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(p.name LIKE ? OR p.code LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($cat_filter) {
    $where[] = "p.category_id = ?";
    $params[] = $cat_filter;
}

$where_sql = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$where_sql}");
$stmt->execute($params);
$total = $stmt->fetchColumn();

$pagination = paginate($total, 10, $page);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE {$where_sql} ORDER BY p.created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-seedling"></i> Quản lý sản phẩm</h2>
    <a href="product-form.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm sản phẩm</a>
</div>

<!-- Filter -->
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, mã SP..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="0">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ảnh</th><th>Mã SP</th><th>Tên</th><th>Danh mục</th>
                        <th>Giá nhập</th><th>% LN</th><th>Giá bán</th>
                        <th>Tồn kho</th><th>Trạng thái</th><th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><img src="../<?= getProductImage($p['image']) ?>" width="50" height="50" class="rounded" style="object-fit:cover;"></td>
                        <td><code><?= htmlspecialchars($p['code']) ?></code></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td><?= formatPrice($p['import_price']) ?></td>
                        <td><?= $p['profit_margin'] ?>%</td>
                        <td class="text-danger fw-bold"><?= formatPrice(getSellingPrice($p['import_price'], $p['profit_margin'])) ?></td>
                        <td>
                            <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?>
                                <span class="badge bg-warning"><?= $p['stock'] ?></span>
                            <?php elseif ($p['stock'] == 0): ?>
                                <span class="badge bg-danger">0</span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['status'] === 'active'): ?>
                                <span class="badge bg-success">Đang bán</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="product-form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                            <a href="product-delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Xóa"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$base = "products.php?search=" . urlencode($search) . "&category_id={$cat_filter}";
echo renderPagination($pagination, $base);
?>

<?php include 'footer.php'; ?>