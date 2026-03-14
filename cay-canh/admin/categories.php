<?php
$page_title = 'Quản lý danh mục';
require_once '../functions.php';

// Xử lý thêm/sửa/xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            setFlash('danger', 'Tên danh mục không được trống');
        } else {
            // Upload image
            $image = $_POST['old_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploaded = uploadImage($_FILES['image']);
                if ($uploaded) $image = $uploaded;
            }
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $image]);
                setFlash('success', 'Thêm danh mục thành công');
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name=?, description=?, image=? WHERE id=?");
                $stmt->execute([$name, $description, $image, $id]);
                setFlash('success', 'Cập nhật danh mục thành công');
            }
        }
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            setFlash('danger', 'Không thể xóa danh mục đang có sản phẩm');
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Đã xóa danh mục');
        }
    }
    
    header('Location: categories.php');
    exit;
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name")->fetchAll();

// Edit mode
$edit_cat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_cat = $stmt->fetch();
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-tags"></i> Quản lý danh mục</h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><?= $edit_cat ? 'Sửa' : 'Thêm' ?> danh mục</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?= $edit_cat ? 'edit' : 'add' ?>">
                    <?php if ($edit_cat): ?>
                    <input type="hidden" name="id" value="<?= $edit_cat['id'] ?>">
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($edit_cat['image'] ?? '') ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_cat['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_cat['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hình ảnh</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($edit_cat && $edit_cat['image']): ?>
                        <small class="text-muted">Ảnh hiện tại: <?= $edit_cat['image'] ?></small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
                    <?php if ($edit_cat): ?>
                    <a href="categories.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Tên danh mục</th><th>Mô tả</th><th>Số SP</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                            <td><?= htmlspecialchars(mb_substr($c['description'] ?? '', 0, 50)) ?></td>
                            <td><span class="badge bg-info"><?= $c['product_count'] ?></span></td>
                            <td>
                                <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>