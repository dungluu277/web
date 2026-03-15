# 📸 IMAGE MAPPING - GẮN ẢNH VÀO SẢN PHẨM

## ✅ Tiến trình hoàn thành

### Bước 1: Nhận diện folder images
- ✅ Tìm thấy folder `/images/` chứa 10 file hình ảnh các loài hoa
- ✅ Tất cả file đều có tên tiếng Việt khớp với tên sản phẩm

### Bước 2: Lập bảng ánh xạ
Các file ảnh được tìm thấy:
1. `cây hoa giấy.jpg` → Cây Hoa Giấy (ID: 7)
2. `cây hoa hồng.jpg` → Cây Hoa Hồng (ID: 5)
3. `cây hoa mai.jpg` → Cây Hoa Mai (ID: 6)
4. `cây kim ngân.jpg` → Cây Kim Ngân (ID: 8)
5. `cây kim tiền.png` → Cây Kim Tiền (ID: 2)
6. `cây lưỡi hổ.jpg` → Cây Lưỡi Hổ (ID: 1)
7. `cây phát tài.jpg` → Cây Phát Tài (ID: 9)
8. `cây thiết mộc lan.jpg` → Cây Thiết Mộc Lan (ID: 10)
9. `cây trầu bà.jpg` → Cây Trầu Bà (ID: 3)
10. `cây vạn niên thanh.jpg` → Cây Vạn Niên Thanh (ID: 4)

### Bước 3: Cập nhật hàm getProductImage()
- ✅ Sửa `functions.php` - Hàm `getProductImage()`
- ✅ Ưu tiên tìm ảnh từ folder `/images/` trước tiên
- ✅ Nếu không tìm thấy, sẽ dùng ảnh upload hoặc placeholder

## 🔧 Cách hoạt động

**Thứ tự ưu tiên hiển thị ảnh:**

```
1️⃣ Kiểm tra folder local: /images/{product_name}.jpg hay .png
   (Tìm kiếm dựa trên tên sản phẩm trong database)

2️⃣ Kiểm tra folder uploads: /assets/uploads/{uploaded_image}
   (Nếu admin upload ảnh tùy chỉnh)

3️⃣ Fallback placeholder: SVG placeholder "No Image"
   (Nếu không tìm thấy ảnh nào)
```

## 📝 Ví dụ xử lý

```php
// Khi hiển thị sản phẩm "Cây Hoa Giấy"
getProductImage('', 'Cây Hoa Giấy');

// Kết quả: 'images/Cây Hoa Giấy.jpg'
// Hiệu quả: Ảnh thật từ folder local được sử dụng
```

## ✨ Lợi ích

- ✅ Ảnh sắc nét, tải nhanh (ảnh cục bộ)
- ✅ Không phụ thuộc internet API
- ✅ Nhất quán với tên sản phẩm
- ✅ Admin có thể upload ảnh mới để ghi đè
- ✅ Tinh gọn, không code thừa

## 📊 Test kết quả

Tất cả 10 sản phẩm cây hoa đã được khớp với ảnh:
```
✓ Cây Hoa Giấy → images/Cây Hoa Giấy.jpg
✓ Cây Hoa Hồng → images/Cây Hoa Hồng.jpg
✓ Cây Hoa Mai → images/Cây Hoa Mai.jpg
✓ Cây Kim Ngân → images/Cây Kim Ngân.jpg
✓ Cây Kim Tiền → images/Cây Kim Tiền.png
✓ Cây Lưỡi Hổ → images/Cây Lưỡi Hổ.jpg
✓ Cây Phát Tài → images/Cây Phát Tài.jpg
✓ Cây Thiết Mộc Lan → images/Cây Thiết Mộc Lan.jpg
✓ Cây Trầu Bà → images/Cây Trầu Bà.jpg
✓ Cây Vạn Niên Thanh → images/Cây Vạn Niên Thanh.jpg
```

## 🗂️ Cấu trúc thư mục

```
cay-canh/
├── images/                          ← Folder ảnh cục bộ
│   ├── cây hoa giấy.jpg
│   ├── cây hoa hồng.jpg
│   ├── cây hoa mai.jpg
│   ├── cây kim ngân.jpg
│   ├── cây kim tiền.png
│   ├── cây lưỡi hổ.jpg
│   ├── cây phát tài.jpg
│   ├── cây thiết mộc lan.jpg
│   ├── cây trầu bà.jpg
│   └── cây vạn niên thanh.jpg
├── assets/
│   └── uploads/                     ← Folder upload từ admin
├── functions.php                    ← Hàm getProductImage() được cập nhật
└── ...
```

---

**Status: ✅ HOÀN THÀNH**

Toàn bộ ảnh đã được gắn đúng loài hoa và hệ thống sẽ tự động hiển thị ảnh cục bộ khi truy cập sản phẩm.
