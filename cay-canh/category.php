<?php
require_once 'functions.php';

$cat_id = intval($_GET['id'] ?? 0);
$page = intval($_GET['page'] ?? 1);

// Lấy thông tin danh mục
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$cat_id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit;
}

$page_title = $category['name'];

// Đếm tổng sản phẩm
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
$stmt->execute([$cat_id]);
$total = $stmt->fetchColumn();

$pagination = paginate($total, ITEMS_PER_PAGE, $page);

// Lấy sản phẩm có phân trang
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$cat_id, $pagination['per_page'], $pagination['offset']]);
$products = $stmt->fetchAll();

include 'header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item active"><?= clean($category['name']) ?></li>
    </ol>
</nav>

<h2 class="text-success mb-3"><i class="fas fa-seedling"></i> <?= clean($category['name']) ?></h2>
<p class="text-muted"><?= clean($category['description']) ?></p>
<p>Hiển thị <?= $total ?> sản phẩm</p>

<?php if (empty($products)): ?>
<div class="alert alert-info">Chưa có sản phẩm nào trong danh mục này.</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-3">
        <div class="card product-card h-100 shadow-sm">
            <a href="product-detail.php?id=<?= $p['id'] ?>">
                <img src="<?= getProductImage($p['image']) ?>" class="card-img-top" alt="<?= clean($p['name']) ?>" style="height:200px;object-fit:cover;">
            </a>
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">
                    <a href="product-detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= clean($p['name']) ?></a>
                </h6>
                <small class="text-muted">Mã: <?= clean($p['code']) ?></small>
                <p class="text-danger fw-bold mt-auto mb-1"><?= formatPrice(getSellingPrice($p['import_price'], $p['profit_margin'])) ?></p>
                <?php if ($p['stock'] > 0): ?>
                    <span class="badge bg-success mb-2">Còn <?= $p['stock'] ?> <?= clean($p['unit']) ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary mb-2">Hết hàng</span>
                <?php endif; ?>
                <a href="product-detail.php?id=<?= $p['id'] ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?= renderPagination($pagination, "category.php?id={$cat_id}") ?>
<?php endif; ?>

<?php include 'footer.php'; ?>