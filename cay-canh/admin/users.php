<?php
$page_title = 'Quản lý người dùng';
require_once '../functions.php';

// Xử lý khóa/mở khóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_lock' && $user_id > 0) {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$user_id, $_SESSION['admin_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $new_status = $user['status'] === 'active' ? 'locked' : 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            setFlash('success', 'Đã cập nhật trạng thái tài khoản');
        }
    }
    
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY role DESC, created_at DESC")->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-users"></i> Quản lý người dùng</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr><th>ID</th><th>Username</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                    <td><?= htmlspecialchars($u['fullname']) ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'info' ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?></span></td>
                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $u['status'] === 'active' ? 'Hoạt động' : 'Đã khóa' ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userDetailModal" onclick="loadUserDetails(<?= htmlspecialchars(json_encode([
                            'id' => $u['id'],
                            'username' => $u['username'],
                            'fullname' => $u['fullname'],
                            'email' => $u['email'],
                            'phone' => $u['phone'],
                            'address' => $u['address'] ?? '',
                            'district' => $u['district'] ?? '',
                            'city' => $u['city'] ?? '',
                            'role' => $u['role'],
                            'status' => $u['status'],
                            'created_at' => $u['created_at'] ?? ''
                        ])) ?>, 'userDetail')" title="Xem thông tin">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="toggle_lock">
                            <button type="submit" class="btn btn-sm btn-<?= $u['status'] === 'active' ? 'warning' : 'success' ?>" title="<?= $u['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?>">
                                <i class="fas fa-<?= $u['status'] === 'active' ? 'lock' : 'unlock' ?>"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal xem thông tin khách hàng -->
<div class="modal fade" id="userDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mã KH</label>
                            <p id="detail_id" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên đăng nhập</label>
                            <p id="detail_username" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ tên</label>
                            <p id="detail_fullname" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p id="detail_email" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <p id="detail_phone" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <p id="detail_address" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quận/Huyện</label>
                            <p id="detail_district" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tỉnh/Thành phố</label>
                            <p id="detail_city" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Vai trò</label>
                            <p id="detail_role" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <p id="detail_status" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ngày tạo</label>
                            <p id="detail_created_at" class="form-control-plaintext border-bottom pb-2">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadUserDetails(user, target) {
    if (target === 'userDetail') {
        document.getElementById('detail_id').textContent = user.id || '-';
        document.getElementById('detail_username').textContent = user.username || '-';
        document.getElementById('detail_fullname').textContent = user.fullname || '-';
        document.getElementById('detail_email').textContent = user.email || '-';
        document.getElementById('detail_phone').textContent = user.phone || '-';
        document.getElementById('detail_address').textContent = user.address || '-';
        document.getElementById('detail_district').textContent = user.district || '-';
        document.getElementById('detail_city').textContent = user.city || '-';
        
        const roleText = user.role === 'admin' ? '👤 Admin' : '🛍️ Khách hàng';
        document.getElementById('detail_role').innerHTML = `<span class="badge bg-${user.role === 'admin' ? 'danger' : 'info'}">${roleText}</span>`;
        
        const statusText = user.status === 'active' ? 'Hoạt động' : 'Đã khóa';
        document.getElementById('detail_status').innerHTML = `<span class="badge bg-${user.status === 'active' ? 'success' : 'secondary'}">${statusText}</span>`;
        
        document.getElementById('detail_created_at').textContent = user.created_at ? new Date(user.created_at).toLocaleDateString('vi-VN') : '-';
    }
}
</script>

<?php include 'footer.php'; ?>