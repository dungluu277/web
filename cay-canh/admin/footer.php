         </div>
        </div>
    </div>

    <!-- Modal thông tin quản trị viên -->
    <div class="modal fade" id="adminInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user"></i> Thông tin quản trị viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($admin_info)): ?>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Họ tên:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['fullname']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Username:</div>
                        <div class="col-8"><code><?= htmlspecialchars($admin_info['username']) ?></code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Email:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">SĐT:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['phone'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Địa chỉ:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['address'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Quận/Huyện:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['district'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Tỉnh/TP:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['city'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Vai trò:</div>
                        <div class="col-8"><span class="badge bg-danger">Admin</span></div>
                    </div>
                    <div class="row">
                        <div class="col-4 fw-bold">Ngày tạo:</div>
                        <div class="col-8"><?= htmlspecialchars($admin_info['created_at'] ?? 'N/A') ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas - Lịch sử nhập hàng -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="importHistoryCanvas">
        <div class="offcanvas-header bg-success text-white">
            <h5 class="offcanvas-title" id="importHistoryTitle">Lịch sử nhập hàng</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div id="importHistoryContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function loadImportHistory(productId, productName, productCode) {
            fetch('get-import-history.php?product_id=' + productId)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.batches.length > 0) {
                        let html = '<div style="padding: 15px;">';
                        data.batches.forEach((batch, index) => {
                            html += `
                                <div style="margin-bottom: 20px; border: 1px solid #dee2e6; border-radius: 5px; overflow: hidden;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #dee2e6; padding: 12px 15px; background: #f8f9fa;">
                                        <div><strong>Phiếu nhập:</strong></div>
                                        <div><code>#${batch.receipt_id}</code></div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #dee2e6; padding: 12px 15px;">
                                        <div><strong>Ngày nhập:</strong></div>
                                        <div>${batch.import_date}</div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #dee2e6; padding: 12px 15px; background: #f8f9fa;">
                                        <div><strong>Số lượng:</strong></div>
                                        <div>${batch.quantity}</div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #dee2e6; padding: 12px 15px;">
                                        <div><strong>Giá nhập:</strong></div>
                                        <div>${batch.import_price}</div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #dee2e6; padding: 12px 15px; background: #f8f9fa;">
                                        <div><strong>% Lợi nhuận:</strong></div>
                                        <div>${batch.profit_margin}%</div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; padding: 12px 15px;">
                                        <div><strong>Giá bán:</strong></div>
                                        <div style="color: #dc3545; font-weight: bold;">${batch.selling_price}</div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        
                        document.getElementById('importHistoryContent').innerHTML = html;
                        document.getElementById('importHistoryTitle').innerHTML = `
                            <i class="fas fa-history"></i> ${productName}<br>
                            <small>${productCode}</small>
                        `;
                    } else {
                        document.getElementById('importHistoryContent').innerHTML = `
                            <div class="alert alert-info m-3">
                                <i class="fas fa-info-circle"></i> Chưa có lô hàng nhập cho sản phẩm này
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    document.getElementById('importHistoryContent').innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="fas fa-exclamation-circle"></i> Lỗi tải dữ liệu
                        </div>
                    `;
                    console.error(err);
                });
        }
    </script>
</body>
</html>