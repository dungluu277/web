<?php
require_once '../functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM import_receipts WHERE id = ? AND status = 'draft'");
    $stmt->execute([$id]);
    $receipt = $stmt->fetch();
    
    if ($receipt) {
        try {
            $pdo->beginTransaction();
            
            // Lấy chi tiết phiếu nhập
            $stmt = $pdo->prepare("SELECT * FROM import_receipt_details WHERE receipt_id = ?");
            $stmt->execute([$id]);
            $details = $stmt->fetchAll();
            
            // Cập nhật giá nhập bình quân và tồn kho cho từng sản phẩm
            foreach ($details as $d) {
                updateImportPrice($pdo, $d['product_id'], $d['quantity'], $d['import_price']);
            }
            
            // Đánh dấu phiếu hoàn thành
            $stmt = $pdo->prepare("UPDATE import_receipts SET status = 'completed' WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            setFlash('success', 'Hoàn thành phiếu nhập #' . $id . ' - Đã cập nhật giá nhập bình quân và tồn kho');
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }
    } else {
        setFlash('danger', 'Phiếu nhập không tồn tại hoặc đã hoàn thành');
    }
}

header('Location: imports.php');
exit;