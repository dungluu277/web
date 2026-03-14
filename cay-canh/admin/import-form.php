<?php
$page_title = 'Phiếu nhập hàng';
require_once '../functions.php';

$id = intval($_GET['id'] ?? 0);
$view_only = isset($_GET['view']);
$receipt = null;
$details = [];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM import_receipts WHERE id = ?");
    $stmt->execute([$id]);
    $receipt = $stmt->fetch();
    if (!$receipt) { header('Location: imports.php'); exit; }
    
    if ($receipt['status'] === 'completed') $view_only = true;
    
    $stmt = $pdo->prepare("SELECT ird.*, p.name as product_name, p.code as product_code FROM import_receipt_details ird JOIN products p ON ird.product_id = p.id WHERE ird.receipt_id = ?");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
}

$products = $pdo->query("SELECT id, code, name FROM products WHERE status = 'active' ORDER BY name")->fetchAll();

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$view_only) {
    $import_date = $_POST['import_date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['import_price'] ?? [];
    
    if (empty($product_ids) || empty($product_ids[0])) {
        setFlash('danger', 'Phải có ít nhất 1 sản phẩm');
        header("Location: import-form.php" . ($id ? "?id=$id" : ""));
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        if ($id > 0) {
            // Update receipt
            $stmt = $pdo->prepare("UPDATE import_receipts SET import_date=?, notes=? WHERE id=? AND status='draft'");
            $stmt->execute([$import_date, $notes, $id]);
            
            // Delete old details
            $stmt = $pdo->prepare("DELETE FROM import_receipt_details WHERE receipt_id = ?");
            $stmt->execute([$id]);
        } else {
            // Create new receipt
            $stmt = $pdo->prepare("INSERT INTO import_receipts (import_date, notes, status) VALUES (?, ?, 'draft')");
            $stmt->execute([$import_date, $notes]);
            $id = $pdo->lastInsertId();
        }
        
        // Insert details
        $stmt = $pdo->prepare("INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($product_ids); $i++) {
            if (!empty($product_ids[$i]) && intval($quantities[$i]) > 0) {
                $stmt->execute([$id, intval($product_ids[$i]), intval($quantities[$i]), floatval($prices[$i])]);
            }
        }
        
        $pdo->commit();
        setFlash('success', 'Lưu phiếu nhập thành công');
        header('Location: imports.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Lỗi: ' . $e->getMessage());
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-truck-loading"></i> <?= $id ? ($view_only ? 'Chi tiết' : 'Sửa') : 'Tạo' ?> phiếu nhập #<?= $id ?: 'Mới' ?></h2>
    <a href="imports.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<form method="POST">
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Ngày nhập</label>
                    <input type="date" name="import_date" class="form-control" value="<?= $receipt['import_date'] ?? date('Y-m-d') ?>" <?= $view_only ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Ghi chú</label>
                    <input type="text" name="notes" class="form-control" value="<?= htmlspecialchars($receipt['notes'] ?? '') ?>" <?= $view_only ? 'disabled' : '' ?>>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết sản phẩm nhập</h5>
            <?php if (!$view_only): ?>
            <button type="button" class="btn btn-success btn-sm" id="addImportRow"><i class="fas fa-plus"></i> Thêm dòng</button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr><th>Sản phẩm</th><th width="150">Số lượng</th><th width="200">Giá nhập</th><?php if (!$view_only): ?><th width="50"></th><?php endif; ?></tr>
                </thead>
                <tbody id="importItems">
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $d): ?>
                        <tr class="import-row">
                            <td>
                                <?php if ($view_only): ?>
                                    <?= htmlspecialchars($d['product_code'] . ' - ' . $d['product_name']) ?>
                                <?php else: ?>
                                <select name="product_id[]" class="form-select" required>
                                    <option value="">-- Chọn SP --</option>
                                    <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $d['product_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['code'] . ' - ' . $p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php endif; ?>
                            </td>
                            <td><input type="number" name="quantity[]" class="form-control" value="<?= $d['quantity'] ?>" min="1" required <?= $view_only ? 'disabled' : '' ?>></td>
                            <td><input type="number" name="import_price[]" class="form-control" value="<?= $d['import_price'] ?>" min="0" required <?= $view_only ? 'disabled' : '' ?>></td>
                            <?php if (!$view_only): ?><td><button type="button" class="btn btn-sm btn-danger" onclick="removeImportRow(this)"><i class="fas fa-times"></i></button></td><?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="import-row">
                            <td>
                                <select name="product_id[]" class="form-select" required>
                                    <option value="">-- Chọn SP --</option>
                                    <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['code'] . ' - ' . $p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="form-control" min="1" required></td>
                            <td><input type="number" name="import_price[]" class="form-control" min="0" required></td>
                            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeImportRow(this)"><i class="fas fa-times"></i></button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!$view_only): ?>
    <div class="mt-3">
        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Lưu phiếu nhập</button>
    </div>
    <?php endif; ?>
</form>

<?php include 'footer.php'; ?>
