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

// ============ EMAIL & PASSWORD RESET ============

function sendPasswordResetEmail($email, $reset_token) {
    require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SITE_NAME);
        $mail->addAddress($email);
        
        // Content
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $reset_url = $protocol . "://" . $host . "/cay-canh/reset-password.php?token=" . $reset_token;
        
        $mail->isHTML(true);
        $mail->Subject = "Đặt lại mật khẩu - " . SITE_NAME;
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 20px; }
                .button-container { text-align: center; padding: 20px 0; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
                .warning { color: #e74c3c; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Đặt lại mật khẩu</h1>
                </div>
                <div class='content'>
                    <p>Chào bạn,</p>
                    <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Nhấp vào nút bên dưới để đặt lại mật khẩu của bạn:</p>
                    
                    <div class='button-container'>
                        <a href='" . htmlspecialchars($reset_url) . "' class='button'>Đặt lại mật khẩu</a>
                    </div>
                    
                    <p>Hoặc sao chép liên kết này vào trình duyệt của bạn:</p>
                    <p style='word-break: break-all;'>" . htmlspecialchars($reset_url) . "</p>
                    
                    <p class='warning'><strong>⚠️ Lưu ý:</strong> Liên kết này sẽ hết hạn sau 24 giờ.</p>
                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        
        // Log success
        $log_file = __DIR__ . '/logs/email_log.txt';
        @mkdir(dirname($log_file), 0755, true);
        $log_content = "[" . date('Y-m-d H:i:s') . "] SUCCESS: Password reset email sent to $email\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        // Log error
        $log_file = __DIR__ . '/logs/email_log.txt';
        @mkdir(dirname($log_file), 0755, true);
        $log_content = "[" . date('Y-m-d H:i:s') . "] ERROR: Failed to send password reset email to $email - " . $mail->ErrorInfo . "\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        return false;
    }
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

function getProductImage($image, $product_name = '') {
    // 1. Kiểm tra ảnh từ folder images cục bộ
    if (!empty($product_name)) {
        // Thử .jpg trước
        $local_image_jpg = __DIR__ . '/images/' . trim($product_name) . '.jpg';
        if (file_exists($local_image_jpg)) {
            $timestamp = filemtime($local_image_jpg);
            return 'images/' . trim($product_name) . '.jpg?v=' . $timestamp;
        }
        
        // Thử .png
        $local_image_png = __DIR__ . '/images/' . trim($product_name) . '.png';
        if (file_exists($local_image_png)) {
            $timestamp = filemtime($local_image_png);
            return 'images/' . trim($product_name) . '.png?v=' . $timestamp;
        }
    }
    
    // 2. Nếu có file upload từ admin, dùng file đó
    if ($image && file_exists(UPLOAD_DIR . $image)) {
        $timestamp = filemtime(UPLOAD_DIR . $image);
        return 'assets/uploads/' . $image . '?v=' . $timestamp;
    }
    
    // 3. Fallback: ảnh placeholder generic
    return 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22400%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22%23888%22%3ENo Image%3C/text%3E%3C/svg%3E';
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