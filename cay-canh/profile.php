<?php
$page_title = 'Thông tin cá nhân';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser($pdo);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $city = trim($_POST['city'] ?? '');
        
        // Validate
        if (empty($fullname)) $errors[] = 'Họ tên không được để trống';
        if (empty($phone)) $errors[] = 'Số điện thoại không được để trống';
        if (!preg_match('/^[0-9]{10,11}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
        if (empty($address)) $errors[] = 'Địa chỉ không được để trống';
        if (empty($district)) $errors[] = 'Quận/Huyện không được để trống';
        if (empty($city)) $errors[] = 'Tỉnh/Thành phố không được để trống';
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET fullname=?, email=?, phone=?, address=?, district=?, city=? WHERE id=?");
            $stmt->execute([$fullname, $email, $phone, $address, $district, $city, $_SESSION['user_id']]);
            
            // Update session
            $_SESSION['user_fullname'] = $fullname;
            
            $user = getCurrentUser($pdo);
            setFlash('success', 'Cập nhật thông tin thành công!');
            header('Location: profile.php');
            exit;
        }
    }
    
    if ($action === 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($old_password)) $errors[] = 'Vui lòng nhập mật khẩu cũ';
        if (empty($new_password)) $errors[] = 'Vui lòng nhập mật khẩu mới';
        if (empty($confirm_password)) $errors[] = 'Vui lòng xác nhận mật khẩu mới';
        if (strlen($new_password) < 6) $errors[] = 'Mật khẩu mới tối thiểu 6 ký tự';
        if ($new_password !== $confirm_password) $errors[] = 'Xác nhận mật khẩu không khớp';
        
        if (empty($errors)) {
            if (md5($old_password) !== $user['password']) {
                $errors[] = 'Mật khẩu cũ không đúng';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->execute([md5($new_password), $_SESSION['user_id']]);
                setFlash('success', 'Đổi mật khẩu thành công!');
                header('Location: profile.php');
                exit;
            }
        }
    }
}

include 'header.php';
?>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-circle"></i> Thông tin tài khoản</h5>
            </div>
            <div class="card-body text-center">
                <div style="background: linear-gradient(135deg, #27ae60, #229954); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 15px;">
                    <i class="fas fa-user"></i>
                </div>
                <h5><?= clean($user['fullname']) ?></h5>
                <p class="text-muted">@<?= clean($user['username']) ?></p>
                <hr>
                <p><small class="text-muted">Ngày tham gia:</small><br><?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                <p><small class="text-muted">Trạng thái:</small><br>
                    <?php if ($user['status'] === 'active'): ?>
                        <span class="badge bg-success">Kích hoạt</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Đã khóa</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Thông tin cá nhân -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-address-card"></i> Thông tin cá nhân</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                        <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" id="profileForm">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" value="<?= clean($user['fullname']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= clean($user['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?= clean($user['phone']) ?>" required pattern="[0-9]{10,11}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" value="<?= clean($user['address']) ?>" required placeholder="Số nhà, tên đường...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                            <select name="city" id="citySelect" class="form-select" required>
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                            <select name="district" id="districtSelect" class="form-select" required>
                                <option value="">-- Vui lòng chọn Tỉnh trước --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu thay đổi</button>
                        <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-lock"></i> Đổi mật khẩu</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu cũ <span class="text-danger">*</span></label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-info"><i class="fas fa-key"></i> Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="data/vietnam-addresses.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const citySelect = document.getElementById('citySelect');
    const districtSelect = document.getElementById('districtSelect');
    
    // Populate cities
    Object.keys(vietnamAddresses).sort().forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        citySelect.appendChild(option);
    });
    
    // Handle city change
    citySelect.addEventListener('change', function() {
        districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
        
        if (this.value && vietnamAddresses[this.value]) {
            vietnamAddresses[this.value].forEach(district => {
                const option = document.createElement('option');
                option.value = district;
                option.textContent = district;
                districtSelect.appendChild(option);
            });
        }
    });
    
    // Restore current values
    const currentCity = "<?= clean($user['city'] ?? '') ?>";
    const currentDistrict = "<?= clean($user['district'] ?? '') ?>";
    
    if (currentCity) {
        citySelect.value = currentCity;
        citySelect.dispatchEvent(new Event('change'));
        setTimeout(() => {
            districtSelect.value = currentDistrict;
        }, 100);
    }
});
</script>

<?php include 'footer.php'; ?>
