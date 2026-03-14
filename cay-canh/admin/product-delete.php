<?php
require_once '../functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Kiểm tra sản phẩm đã được nhập hàng chưa
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM import_receipt_details WHERE product_id = ?");
    $stmt->execute([$id]);
    $has_imports = $stmt->fetchColumn() > 0;
    
    // Kiểm tra sản phẩm đã được đặt hàng chưa
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_details WHERE product_id = ?");
    $stmt->execute([$id]);
    $has_orders = $stmt->fetchColumn() > 0;
    
    if (!$has_imports && !$has_orders) {
        // Xóa hẳn khỏi DB
        $stmt = $pdo->prepare("DELETE FROM cart WHERE product_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Đã xóa sản phẩm');
    } else {
        // Đánh dấu ẩn
        $stmt = $pdo->prepare("UPDATE products SET status = 'hidden' WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('warning', 'Sản phẩm đã có dữ liệu liên quan, đã chuyển sang trạng thái ẩn thay vì xóa');
    }
}

header('Location: products.php');
exit;