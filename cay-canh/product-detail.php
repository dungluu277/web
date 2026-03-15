<?php
require_once 'functions.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$page_title = $product['name'];
$selling_price = getSellingPrice($product['import_price'], $product['profit_margin']);

// Sản phẩm liên quan
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 4");
$stmt->execute([$product['category_id'], $id]);
$related = $stmt->fetchAll();

// Xử lý thêm vào giỏ
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=product-detail.php?id=' . $id);
        exit;
    }
    $qty = max(1, intval($_POST['quantity'] ?? 1));
    if ($qty > $product['stock']) {
        $message = '<div class="alert alert-danger">Số lượng vượt quá tồn kho!</div>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->execute([$_SESSION['user_id'], $id, $qty, $qty]);
        setFlash('success', 'Đã thêm sản phẩm vào giỏ hàng!');
        header('Location: product-detail.php?id=' . $id);
        exit;
    }
}

include 'header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="category.php?id=<?= $product['category_id'] ?>"><?= clean($product['category_name']) ?></a></li>
        <li class="breadcrumb-item active"><?= clean($product['name']) ?></li>
    </ol>
</nav>

<?= $message ?>

<div class="row">
    <div class="col-md-5">
        <img src="<?= getProductImage($product['image'], $product['name']) ?>" class="img-fluid rounded shadow" alt="<?= clean($product['name']) ?>">
    </div>
    <div class="col-md-7">
        <h2><?= clean($product['name']) ?></h2>
        <p class="text-muted">Mã SP: <?= clean($product['code']) ?> | Danh mục: <a href="category.php?id=<?= $product['category_id'] ?>"><?= clean($product['category_name']) ?></a></p>
        
        <h3 class="text-danger fw-bold"><?= formatPrice($selling_price) ?></h3>
        
        <table class="table table-bordered mt-3">
            <tr><th width="150">Đơn vị tính</th><td><?= clean($product['unit']) ?></td></tr>
            <tr>
                <th>Tình trạng</th>
                <td>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="text-success fw-bold">Còn hàng (<?= $product['stock'] ?> <?= clean($product['unit']) ?>)</span>
                    <?php else: ?>
                        <span class="text-danger fw-bold">Hết hàng</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <?php if ($product['stock'] > 0 && $product['status'] === 'active'): ?>
        <form method="POST" class="mt-3">
            <div class="d-flex align-items-center gap-3">
                <label class="fw-bold">Số lượng:</label>
                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="form-control" style="width:100px;" required>
                <button type="submit" name="add_to_cart" class="btn btn-success btn-lg">
                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                </button>
            </div>
        </form>
        <?php endif; ?>

        <div class="mt-4">
            <h5>Mô tả sản phẩm</h5>
            <p><?= nl2br(clean($product['description'])) ?></p>
        </div>
    </div>
</div>

<?php if (!empty($related)): ?>
<hr class="my-4">
<h4 class="text-success"><i class="fas fa-seedling"></i> Sản phẩm liên quan</h4>
<div class="row g-3">
    <?php foreach ($related as $r): ?>
    <div class="col-6 col-md-3">
        <div class="card product-card h-100 shadow-sm">
            <a href="product-detail.php?id=<?= $r['id'] ?>">
                <img src="<?= getProductImage($r['image'], $r['name']) ?>" class="card-img-top" style="height:180px;object-fit:cover;">
            </a>
            <div class="card-body">
                <h6><a href="product-detail.php?id=<?= $r['id'] ?>" class="text-decoration-none text-dark"><?= clean($r['name']) ?></a></h6>
                <p class="text-danger fw-bold"><?= formatPrice(getSellingPrice($r['import_price'], $r['profit_margin'])) ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>