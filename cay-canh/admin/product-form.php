<?php
$page_title = 'Sản phẩm';
require_once '../functions.php';

$id = intval($_GET['id'] ?? 0);
$product = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) { header('Location: products.php'); exit; }
    $page_title = 'Sửa sản phẩm';
} else {
    $page_title = 'Thêm sản phẩm';
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => intval($_POST['category_id']),
        'code' => trim($_POST['code']),
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'unit' => trim($_POST['unit']),
        'profit_margin' => floatval($_POST['profit_margin']),
        'status' => $_POST['status'] ?? 'active',
    ];
    
    // Validate
    if (empty($data['code'])) $errors[] = 'Mã sản phẩm không được trống';
    if (empty($data['name'])) $errors[] = 'Tên sản phẩm không được trống';
    if ($data['category_id'] <= 0) $errors[] = 'Chọn danh mục';
    if ($data['profit_margin'] < 0) $errors[] = 'Tỉ lệ lợi nhuận không hợp lệ';
    
    // Check duplicate code
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE code = ? AND id != ?");
        $stmt->execute([$data['code'], $id]);
        if ($stmt->fetch()) $errors[] = 'Mã sản phẩm đã tồn tại';
    }
    
    // Upload image
    $image = $product['image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploaded = uploadImage($_FILES['image']);
        if ($uploaded) $image = $uploaded;
        else $errors[] = 'Lỗi upload hình ảnh (chỉ chấp nhận jpg, png, gif, webp)';
    }
    
    if (empty($errors)) {
        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET category_id=?, code=?, name=?, description=?, unit=?, image=?, profit_margin=?, status=? WHERE id=?");
            $stmt->execute([$data['category_id'], $data['code'], $data['name'], $data['description'], $data['unit'], $image, $data['profit_margin'], $data['status'], $id]);
            setFlash('success', 'Cập nhật sản phẩm thành công');
        } else {
            // Insert
            $stock = max(0, intval($_POST['stock'] ?? 0));
            $import_price = max(0, floatval($_POST['import_price'] ?? 0));
            $stmt = $pdo->prepare("INSERT INTO products (category_id, code, name, description, unit, image, profit_margin, import_price, stock, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$data['category_id'], $data['code'], $data['name'], $data['description'], $data['unit'], $image, $data['profit_margin'], $import_price, $stock, $data['status']]);
            setFlash('success', 'Thêm sản phẩm thành công');
        }
        header('Location: products.php');
        exit;
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-<?= $id ? 'edit' : 'plus' ?>"></i> <?= $page_title ?></h2>
    <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Mã sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($product['code'] ?? $_POST['code'] ?? '') ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name'] ?? $_POST['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? $_POST['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đơn vị tính</label>
                    <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit'] ?? $_POST['unit'] ?? 'Chậu') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tỉ lệ lợi nhuận (%)</label>
                    <input type="number" name="profit_margin" class="form-control" step="0.01" min="0" value="<?= $product['profit_margin'] ?? $_POST['profit_margin'] ?? 30 ?>">
                </div>
                
                <?php if (!$id): ?>
                <div class="col-md-3">
                    <label class="form-label">Giá nhập ban đầu</label>
                    <input type="number" name="import_price" class="form-control" min="0" value="<?= $_POST['import_price'] ?? 0 ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số lượng ban đầu</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?= $_POST['stock'] ?? 0 ?>">
                </div>
                <?php else: ?>
                <div class="col-md-3">
                    <label class="form-label">Giá nhập TB hiện tại</label>
                    <input type="text" class="form-control" value="<?= formatPrice($product['import_price']) ?>" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tồn kho hiện tại</label>
                    <input type="text" class="form-control" value="<?= $product['stock'] ?>" disabled>
                </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <label class="form-label">Hình ảnh</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if ($product && $product['image']): ?>
                    <div class="mt-2">
                        <img src="../<?= getProductImage($product['image']) ?>" width="100" class="rounded">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hiện trạng</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Đang bán</option>
                        <option value="hidden" <?= ($product['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Ẩn / Không bán</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Lưu sản phẩm</button>
                <a href="products.php" class="btn btn-secondary btn-lg">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>