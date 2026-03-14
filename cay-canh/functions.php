<?php
require_once __DIR__ . '/config.php';

// ============ AUTHENTICATION ============

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// ============ PRODUCT & PRICE ============

function getSellingPrice($import_price, $profit_margin) {
    return $import_price * (1 + $profit_margin / 100);
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

// Cập nhật giá nhập bình quân khi nhập hàng
function updateImportPrice($pdo, $product_id, $new_qty, $new_price) {
    $stmt = $pdo->prepare("SELECT stock, import_price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    $old_stock = $product['stock'];
    $old_price = $product['import_price'];
    
    if (($old_stock + $new_qty) > 0) {
        $avg_price = ($old_stock * $old_price + $new_qty * $new_price) / ($old_stock + $new_qty);
    } else {
        $avg_price = $new_price;
    }
    
    $new_stock = $old_stock + $new_qty;
    
    $stmt = $pdo->prepare("UPDATE products SET import_price = ?, stock = ? WHERE id = ?");
    $stmt->execute([round($avg_price, 2), $new_stock, $product_id]);
    
    return $avg_price;
}

// ============ PAGINATION ============

function paginate($total, $per_page, $current_page) {
    $total_pages = max(1, ceil($total / $per_page));
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;
    
    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset
    ];
}

function renderPagination($pagination, $base_url) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    $prev_disabled = $pagination['current_page'] <= 1 ? 'disabled' : '';
    $prev_page = $pagination['current_page'] - 1;
    $separator = strpos($base_url, '?') !== false ? '&' : '?';
    $html .= "<li class='page-item {$prev_disabled}'><a class='page-link' href='{$base_url}{$separator}page={$prev_page}'>«</a></li>";
    
    // Pages
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = $i == $pagination['current_page'] ? 'active' : '';
        $html .= "<li class='page-item {$active}'><a class='page-link' href='{$base_url}{$separator}page={$i}'>{$i}</a></li>";
    }
    
    // Next
    $next_disabled = $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '';
    $next_page = $pagination['current_page'] + 1;
    $html .= "<li class='page-item {$next_disabled}'><a class='page-link' href='{$base_url}{$separator}page={$next_page}'>»</a></li>";
    
    $html .= '</ul></nav>';
    return $html;
}

// ============ CART ============

function getCartCount($pdo) {
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getCartItems($pdo) {
    if (!isLoggedIn()) return [];
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.image, p.import_price, p.profit_margin, p.stock, p.unit, p.code
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll();
}

// ============ IMAGE ============

function uploadImage($file, $dir = null) {
    if ($dir === null) $dir = UPLOAD_DIR;
    
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $target = $dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }
    return false;
}

function getProductImage($image) {
    if ($image && file_exists(UPLOAD_DIR . $image)) {
        return 'assets/uploads/' . $image;
    }
    return 'https://via.placeholder.com/300x300?text=Cây+Cảnh';
}

// ============ SANITIZE ============

function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// ============ FLASH MESSAGE ============

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash() {
    $flash = getFlash();
    if ($flash) {
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

// ============ ORDER STATUS ============

function getOrderStatusText($status) {
    $map = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy'
    ];
    return $map[$status] ?? $status;
}

function getOrderStatusBadge($status) {
    $map = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    $color = $map[$status] ?? 'secondary';
    $text = getOrderStatusText($status);
    return "<span class='badge bg-{$color}'>{$text}</span>";
}

function getPaymentMethodText($method) {
    $map = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'online' => 'Thanh toán trực tuyến'
    ];
    return $map[$method] ?? $method;
}