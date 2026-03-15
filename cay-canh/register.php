<?php
$page_title = 'Đăng ký';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $city = trim($_POST['city'] ?? '');
    
    // Validate
    if (strlen($username) < 4) $errors[] = 'Tên đăng nhập tối thiểu 4 ký tự';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu tối thiểu 6 ký tự';
    if ($password !== $confirm) $errors[] = 'Xác nhận mật khẩu không khớp';
    if (empty($fullname)) $errors[] = 'Họ tên không được để trống';
    if (empty($phone)) $errors[] = 'Số điện thoại không được để trống';
    if (!preg_match('/^[0-9]{10,11}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
    if (empty($address)) $errors[] = 'Địa chỉ không được để trống';
    if (empty($district)) $errors[] = 'Quận/Huyện không được để trống';
    if (empty($city)) $errors[] = 'Tỉnh/Thành phố không được để trống';
    
    // Check username exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Tên đăng nhập đã tồn tại';
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, address, district, city, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'customer', 'active')");
        $stmt->execute([$username, md5($password), $fullname, $email, $phone, $address, $district, $city]);
        
        setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
        header('Location: login.php');
        exit;
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Đăng ký tài khoản</h4>
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

                <form method="POST" id="registerForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= clean($old['username'] ?? '') ?>" required minlength="4">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" value="<?= clean($old['fullname'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= clean($old['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?= clean($old['phone'] ?? '') ?>" required pattern="[0-9]{10,11}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" value="<?= clean($old['address'] ?? '') ?>" required placeholder="Số nhà, tên đường...">
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
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-user-plus"></i> Đăng ký</button>
                        <a href="login.php" class="btn btn-link">Đã có tài khoản? Đăng nhập</a>
                    </div>
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
    
    // Restore old values if form was submitted with error
    const oldCity = "<?= clean($old['city'] ?? '') ?>";
    const oldDistrict = "<?= clean($old['district'] ?? '') ?>";
    
    if (oldCity) {
        citySelect.value = oldCity;
        citySelect.dispatchEvent(new Event('change'));
        setTimeout(() => {
            districtSelect.value = oldDistrict;
        }, 100);
    }
});
</script>

<?php include 'footer.php'; ?>
