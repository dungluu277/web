<?php
$page_title = 'Trang chủ';
require_once 'functions.php';

// Lấy tất cả danh mục kèm sản phẩm (sắp xếp thứ tự cổ)
$categories = $pdo->query("SELECT * FROM categories ORDER BY CASE WHEN name = 'Cây nội thất' THEN 0 WHEN name = 'Cây ngoài thất' THEN 1 WHEN name = 'Cây bonsai' THEN 2 WHEN name = 'Cây phong thủy' THEN 3 WHEN name = 'Sen đá & Xương rồng' THEN 4 WHEN name = 'Phụ kiện chậu cây' THEN 5 ELSE 6 END")->fetchAll();

include 'header.php';
?>

<!-- Banner -->
<div class="bg-success text-white rounded-3 p-5 mb-4 text-center" style="background: linear-gradient(135deg, #28a745, #155724);">
    <h1><i class="fas fa-leaf"></i> Chào mừng đến với <?= SITE_NAME ?></h1>
    <p class="lead">Mang thiên nhiên vào ngôi nhà của bạn với những chậu cây cảnh tuyệt đẹp</p>
    <a href="search.php" class="btn btn-light btn-lg"><i class="fas fa-search"></i> Khám phá ngay</a>
</div>

<?php foreach ($categories as $cat): ?>
<?php
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 4");
    $stmt->execute([$cat['id']]);
    $products = $stmt->fetchAll();
    if (empty($products)) continue;
?>
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-success"><i class="fas fa-seedling"></i> <?= clean($cat['name']) ?></h3>
        <a href="category.php?id=<?= $cat['id'] ?>" class="btn btn-outline-success btn-sm">Xem tất cả <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="row g-3">
        <?php foreach ($products as $p): ?>
        <div class="col-6 col-md-3">
            <div class="card product-card h-100 shadow-sm">
                <a href="product-detail.php?id=<?= $p['id'] ?>">
                    <img src="<?= getProductImage($p['image'], $p['name']) ?>" class="card-img-top" alt="<?= clean($p['name']) ?>" style="height:200px;object-fit:cover;">
                </a>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">
                        <a href="product-detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= clean($p['name']) ?></a>
                    </h6>
                    <p class="text-danger fw-bold mt-auto"><?= formatPrice(getSellingPrice($p['import_price'], $p['profit_margin'])) ?></p>
                    <?php if ($p['stock'] > 0): ?>
                        <span class="badge bg-success mb-2">Còn hàng</span>
                    <?php else: ?>
                        <span class="badge bg-secondary mb-2">Hết hàng</span>
                    <?php endif; ?>
                    <a href="product-detail.php?id=<?= $p['id'] ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>