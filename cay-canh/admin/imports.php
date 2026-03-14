<?php
$page_title = 'Quản lý nhập hàng';
require_once '../functions.php';

$receipts = $pdo->query("
    SELECT ir.*, 
        (SELECT COUNT(*) FROM import_receipt_details WHERE receipt_id = ir.id) as item_count,
        (SELECT SUM(quantity * import_price) FROM import_receipt_details WHERE receipt_id = ir.id) as total_value
    FROM import_receipts ir 
    ORDER BY ir.created_at DESC
")->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-truck-loading"></i> Quản lý nhập hàng</h2>
    <a href="import-form.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo phiếu nhập</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr><th>Mã phiếu</th><th>Ngày nhập</th><th>Số SP</th><th>Tổng giá trị</th><th>Trạng thái</th><th>Ghi chú</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($receipts as $r): ?>
                <tr>
                    <td>#<?= $r['id'] ?></td>
                    <td><?= date('d/m/Y', strtotime($r['import_date'])) ?></td>
                    <td><?= $r['item_count'] ?></td>
                    <td class="text-danger"><?= formatPrice($r['total_value'] ?? 0) ?></td>
                    <td>
                        <?php if ($r['status'] === 'draft'): ?>
                            <span class="badge bg-warning">Nháp</span>
                        <?php else: ?>
                            <span class="badge bg-success">Hoàn thành</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['notes'] ?? '') ?></td>
                    <td>
                        <?php if ($r['status'] === 'draft'): ?>
                            <a href="import-form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="import-complete.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-success btn-delete" title="Hoàn thành phiếu"><i class="fas fa-check"></i></a>
                        <?php else: ?>
                            <a href="import-form.php?id=<?= $r['id'] ?>&view=1" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($receipts)): ?>
                <tr><td colspan="7" class="text-center text-muted">Chưa có phiếu nhập nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
