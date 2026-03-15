# 🌿 Hướng dẫn Cải thiện Trang Web Cây Cảnh Xanh

## ✅ Hoàn thành Các Tính Năng

### 1. **Sửa Form Đăng Ký - Bỏ Quận/Huyện** ✓
- ✅ Bỏ trường "Quận/Huyện" 
- ✅ Thêm **dropdown động** cho "Tỉnh/Thành phố"
- ✅ Thêm **dropdown động** cho "Phường/Xã" (phụ thuộc vào tỉnh được chọn)
- ✅ File dữ liệu: `data/vietnam-addresses.js`
- **Vị trí**: `register.php`

**Cách sử dụng**: Khi người dùng chọn tỉnh, danh sách phường/xã sẽ tự cập nhật.

---

### 2. **Cải Thiện Giao Diện CSS - Làm Trang Web Sinh Động** ✓
**Cực kỳ hiện đại với các tính năng:**

✨ **Animations & Transitions:**
- Fade in/slide animations khi trang load
- Smooth hover effects trên tất cả các nút và card
- Scale & rotate animations khi hover product card
- Pulse effects trên badges

🎨 **Modern Colors & Gradients:**
- Gradient backgrounds xanh chuyên nghiệp
- Linear & radial gradients trên buttons
- Color transitions mịn mà trên hover

📱 **Responsive Design:**
- Hoàn toàn responsive trên mobile, tablet, desktop
- Touch-friendly buttons và inputs

🎯 **Enhanced Elements:**
- Product cards với shadow effects
- Navbar với gradient background
- Làm mới table styling
- Modern form inputs với focus effects
- Footer gradient background

**File cập nhật**: `assets/css/style.css`

---

### 3. **Thêm Hình Ảnh Cây Cảnh** 

Có **3 cách** để thêm ảnh:

#### **Option A: Sử dụng Unsplash API (KHUYÊN DÙNG) 🌟**
Tự động lấy ảnh từ Unsplash (miễn phí, không cần key):
```php
<?php
require_once 'data/plant-images.php';
$image_url = getUnsplashImage('cây phát tài', 400, 400);
?>
<img src="<?= $image_url ?>" alt="Cây Phát Tài">
```

#### **Option B: Sử dụng Placeholder Images**
Ảnh placeholder tạm thời:
```php
<?php
require_once 'data/plant-images.php';
$image_url = getPlaceholderImage(400, 400, 'Cây Phát Tài');
?>
<img src="<?= $image_url ?>" alt="Cây Phát Tài">
```

#### **Option C: Upload Ảnh Local (TỰA NHƯ HIỆN TẠI)**
Lưu ảnh vào `assets/uploads/` và sử dụng đường dẫn file.

---

## 📋 Danh Sách Cây Cảnh Mẫu

Các cây cảnh được kiểm duyệt trong `data/plant-images.php`:

1. 🌱 **Cây Phát Tài** - Cây để bàn
2. 🌵 **Sen Đá** - Cây xương rồng
3. 🌿 **Cây Vạn Tuế** - Cây chống chỉ
4. 💚 **Cây Kim Tiền** - Cây để bàn
5. 🌹 **Hoa Hồng** - Hoa cắt cảnh
6. 🏠 **Cây Trong Nhà Thiên Lý** - Cây lớn
7. 🐍 **Cây Lưỡi Hổ** - Cây lọc không khí
8. 🎋 **Cây Trúc Vàng** - Cây lớn

---

## 🔧 Hướng Dẫn Tích Hợp

### 1. **Cập nhật Database (Nếu cần)**
Bỏ cột `district` từ bảng `users`:
```sql
ALTER TABLE users DROP COLUMN district;
```

### 2. **Sử dụng Ảnh từ Unsplash trong Trang Sản Phẩm**
Thêm vào `index.php` hoặc `product-detail.php`:
```php
<?php require_once 'data/plant-images.php'; ?>
<?php
    $image = !empty($product['image']) 
        ? getProductImage($product['image'])
        : getUnsplashImage($product['name'], 400, 400);
?>
<img src="<?= $image ?>" alt="<?= clean($product['name']) ?>">
```

### 3. **Upload Ảnh Thực**
Vào bảng quản lý `admin/products.php` để thêm ảnh cho từng sản phẩm.

---

## 📂 Files Được Tạo/Sửa

| File | Mô Tả | Trạng Thái |
|------|-------|-----------|
| `register.php` | Sửa form - bỏ district, thêm dropdown | ✅ Hoàn thành |
| `data/vietnam-addresses.js` | Dữ liệu địa chỉ VN | ✅ Tạo mới |
| `assets/css/style.css` | CSS cải thiện - animations, gradients | ✅ Cập nhật |
| `data/plant-images.php` | Helper cho ảnh cây cảnh | ✅ Tạo mới |

---

## 🎨 Lưu Ý Về Styling

Các màu sắc được sử dụng:
- 🟢 **Xanh chính**: `#27ae60` (Green)
- 🟢 **Xanh đậm**: `#1e8449` (Dark Green)
- ⚫ **Nền**: `#1a472a` (Very Dark Green)
- ⚪ **Text chính**: `#2d3748` (Dark Blue-gray)

---

## ✨ Tính Năng Mới

1. ✅ Form dropdown động cho địa chỉ
2. ✅ Animations mịn khi load trang
3. ✅ Hover effects trực quan trên tất cả elements
4. ✅ Gradient backgrounds chuyên nghiệp
5. ✅ Support ảnh từ Unsplash API
6. ✅ Responsive hoàn toàn
7. ✅ Modern UI/UX design

---

## 🚀 Next Steps (Tuỳ Chọn)

- [ ] Thêm thêm cây cảnh vào database `products` table
- [ ] Upload ảnh thực của cây cảnh vào `assets/uploads/`
- [ ] Tối ưu hóa ảnh (compress, optimize)
- [ ] Thêm search filter theo loại cây
- [ ] Thêm review/rating cho sản phẩm
- [ ] Implement wishlist feature

---

**Hãy kiểm tra trang web của bạn ngay bây giờ!** 🌟
