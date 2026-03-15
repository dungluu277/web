<?php
$page_title = 'Tìm kiếm';
require_once 'functions.php';

$keyword = trim($_GET['keyword'] ?? '');
$cat_id = intval($_GET['category_id'] ?? 0);
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$page = intval($_GET['page'] ?? 1);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$where = ["p.status = 'active'"];
$params = [];

if ($keyword !== '') {
    $where[] = "p.name LIKE ?";
    $params[] = "%{$keyword}%";
}
if ($cat_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $cat_id;
}
if ($price_min !== '' && is_numeric($price_min)) {
    $where[] = "(p.import_price * (1 + p.profit_margin/100)) >= ?";
    $params[] = floatval($price_min);
}
if ($price_max !== '' && is_numeric($price_max)) {
    $where[] = "(p.import_price * (1 + p.profit_margin/100)) <= ?";
    $params[] = floatval($price_max);
}

$where_sql = implode(' AND ', $where);

// Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$where_sql}");
$stmt->execute($params);
$total = $stmt->fetchColumn();

$pagination = paginate($total, ITEMS_PER_PAGE, $page);

// Get products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE {$where_sql} ORDER BY p.created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Build pagination URL
$query_params = [];
if ($keyword) $query_params[] = "keyword=" . urlencode($keyword);
if ($cat_id) $query_params[] = "category_id={$cat_id}";
if ($price_min !== '') $query_params[] = "price_min={$price_min}";
if ($price_max !== '') $query_params[] = "price_max={$price_max}";
$base_url = "search.php?" . implode('&', $query_params);

include 'header.php';
?>

<h2 class="text-success mb-3"><i class="fas fa-search"></i> Tìm kiếm nâng cao</h2>

<!-- Form tìm kiếm nâng cao -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" action="search.php" id="searchForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tên sản phẩm</label>
                    <input type="text" name="keyword" class="form-control" value="<?= clean($keyword) ?>" placeholder="Nhập tên cây cảnh...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Danh mục</label>
                    <select name="category_id" class="form-select">
                        <option value="0">-- Tất cả --</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $cat_id == $c['id'] ? 'selected' : '' ?>><?= clean($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Giá từ</label>
                    <input type="number" name="price_min" class="form-control" value="<?= clean($price_min) ?>" placeholder="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Giá đến</label>
                    <input type="number" name="price_max" class="form-control" value="<?= clean($price_max) ?>" placeholder="10,000,000" min="0">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($keyword || $cat_id || $price_min !== '' || $price_max !== ''): ?>
<p class="text-muted">Tìm thấy <strong><?= $total ?></strong> sản phẩm
    <?php if ($keyword): ?> cho từ khóa "<strong><?= clean($keyword) ?></strong>"<?php endif; ?>
</p>
<?php endif; ?>

<?php if (empty($products) && ($keyword || $cat_id)): ?>
<div class="alert alert-info">Không tìm thấy sản phẩm nào phù hợp.</div>
<?php elseif (!empty($products)): ?>
<div class="row g-3">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-3">
        <div class="card product-card h-100 shadow-sm">
            <a href="product-detail.php?id=<?= $p['id'] ?>">
                <img src="<?= getProductImage($p['image'], $p['name']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
            </a>
            <div class="card-body d-flex flex-column">
                <h6><a href="product-detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= clean($p['name']) ?></a></h6>
                <small class="text-muted"><?= clean($p['category_name']) ?></small>
                <p class="text-danger fw-bold mt-auto mb-1"><?= formatPrice(getSellingPrice($p['import_price'], $p['profit_margin'])) ?></p>
                <?php if ($p['stock'] > 0): ?>
                    <span class="badge bg-success mb-2">Còn hàng</span>
                <?php else: ?>
                    <span class="badge bg-secondary mb-2">Hết hàng</span>
                <?php endif; ?>
                <a href="product-detail.php?id=<?= $p['id'] ?>" class="btn btn-success btn-sm"><i class="fas fa-eye"></i> Chi tiết</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?= renderPagination($pagination, $base_url) ?>
<?php endif; ?>

<?php include 'footer.php'; ?>