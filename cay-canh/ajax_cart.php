<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));

switch ($action) {
    case 'add':
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $quantity]);
        echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng', 'cart_count' => getCartCount($pdo)]);
        break;
        
    case 'remove':
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo)]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}