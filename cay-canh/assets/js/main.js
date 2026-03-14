// ============ FORM VALIDATION ============

document.addEventListener('DOMContentLoaded', function() {
    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = this.querySelector('[name="password"]');
            const confirm = this.querySelector('[name="confirm_password"]');
            const phone = this.querySelector('[name="phone"]');
            const username = this.querySelector('[name="username"]');
            
            let errors = [];
            
            if (username && username.value.trim().length < 4) {
                errors.push('Tên đăng nhập tối thiểu 4 ký tự');
            }
            if (password && password.value.length < 6) {
                errors.push('Mật khẩu tối thiểu 6 ký tự');
            }
            if (password && confirm && password.value !== confirm.value) {
                errors.push('Xác nhận mật khẩu không khớp');
            }
            if (phone && !/^[0-9]{10,11}$/.test(phone.value.trim())) {
                errors.push('Số điện thoại phải gồm 10-11 số');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Lỗi:\n' + errors.join('\n'));
            }
        });
    }
    
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = this.querySelector('[name="username"]').value.trim();
            const password = this.querySelector('[name="password"]').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu');
            }
        });
    }
    
    // Checkout form validation
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const name = this.querySelector('[name="delivery_name"]').value.trim();
            const phone = this.querySelector('[name="delivery_phone"]').value.trim();
            const address = this.querySelector('[name="delivery_address"]').value.trim();
            
            let errors = [];
            if (!name) errors.push('Nhập tên người nhận');
            if (!/^[0-9]{10,11}$/.test(phone)) errors.push('Số điện thoại không hợp lệ');
            if (!address) errors.push('Nhập địa chỉ giao hàng');
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Lỗi:\n' + errors.join('\n'));
            }
        });
    }

    // Search form validation
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const priceMin = this.querySelector('[name="price_min"]');
            const priceMax = this.querySelector('[name="price_max"]');
            
            if (priceMin && priceMax && priceMin.value && priceMax.value) {
                if (parseFloat(priceMin.value) > parseFloat(priceMax.value)) {
                    e.preventDefault();
                    alert('Giá từ phải nhỏ hơn giá đến');
                }
            }
        });
    }
    
    // Admin product form validation
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            const code = this.querySelector('[name="code"]');
            const name = this.querySelector('[name="name"]');
            const profit = this.querySelector('[name="profit_margin"]');
            
            let errors = [];
            if (code && !code.value.trim()) errors.push('Nhập mã sản phẩm');
            if (name && !name.value.trim()) errors.push('Nhập tên sản phẩm');
            if (profit && (parseFloat(profit.value) < 0 || parseFloat(profit.value) > 500)) {
                errors.push('Tỉ lệ lợi nhuận từ 0 đến 500%');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Lỗi:\n' + errors.join('\n'));
            }
        });
    }

    // Import form - add product row
    const addImportRow = document.getElementById('addImportRow');
    if (addImportRow) {
        addImportRow.addEventListener('click', function() {
            const tbody = document.getElementById('importItems');
            const row = document.querySelector('.import-row').cloneNode(true);
            row.querySelectorAll('input').forEach(input => input.value = '');
            row.querySelector('select').selectedIndex = 0;
            tbody.appendChild(row);
        });
    }
    
    // Confirm delete
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn xóa?')) {
                e.preventDefault();
            }
        });
    });
});

// Remove import row
function removeImportRow(btn) {
    const rows = document.querySelectorAll('.import-row');
    if (rows.length > 1) {
        btn.closest('.import-row').remove();
    } else {
        alert('Phải có ít nhất 1 sản phẩm');
    }
}