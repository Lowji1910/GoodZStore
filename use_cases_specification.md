# TÀI LIỆU PHÂN TÍCH USE CASE - HỆ THỐNG GOODZSTORE

## PHẦN I: DANH SÁCH USE CASE (20 USE CASES)

### A. USE CASE NGƯỜI DÙNG (6 USE CASES)
1. **UC-01**: Đăng ký và đăng nhập tài khoản
2. **UC-02**: Xem và tìm kiếm sản phẩm
3. **UC-03**: Quản lý giỏ hàng
4. **UC-04**: Đặt hàng và áp dụng voucher
5. **UC-05**: Đánh giá sản phẩm
6. **UC-06**: Tương tác với AI Chatbot

### B. USE CASE QUẢN TRỊ VIÊN (9 USE CASES)
7. **UC-07**: Quản lý sản phẩm
8. **UC-08**: Quản lý danh mục
9. **UC-09**: Quản lý đơn hàng
10. **UC-10**: Quản lý người dùng
11. **UC-11**: Quản lý voucher
12. **UC-12**: Quản lý đánh giá
13. **UC-13**: Quản lý nội dung (banner, khuyến mãi)
14. **UC-14**: Quản lý dữ liệu AI Training
15. **UC-15**: Xem báo cáo và thống kê

### C. USE CASE HỆ THỐNG AI (4 USE CASES)
16. **UC-16**: Phân tích ý định và xử lý hội thoại
17. **UC-17**: Tư vấn sản phẩm theo ngân sách
18. **UC-18**: Tư vấn size dựa trên số đo
19. **UC-19**: Quản lý lịch sử hội thoại

### D. USE CASE THANH TOÁN (1 USE CASE)
20. **UC-20**: Thanh toán qua VNPay

---

## PHẦN II: ĐẶC TẢ CHI TIẾT

### UC-01: Đăng ký và đăng nhập tài khoản

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Đăng ký và đăng nhập tài khoản |
| **Tác nhân** | Người dùng |
| **Dòng sự kiện chính** | **Đăng ký:**<br>1. Người dùng truy cập trang đăng ký<br>2. Nhập thông tin: họ tên, email, mật khẩu, số điện thoại<br>3. Hệ thống kiểm tra email chưa tồn tại<br>4. Hệ thống mã hóa mật khẩu bằng bcrypt<br>5. Lưu thông tin với role='customer'<br>6. Chuyển đến trang đăng nhập<br><br>**Đăng nhập:**<br>1. Nhập email/SĐT và mật khẩu<br>2. Hệ thống xác thực bằng bcrypt<br>3. Tạo session lưu user_id, role<br>4. Chuyển hướng theo role (admin→dashboard, customer→trang chủ) |
| **Dòng sự kiện ngoại lệ** | 3.1. Email đã tồn tại → Thông báo lỗi<br>4.1. Mật khẩu không đúng → Thông báo "Sai email hoặc mật khẩu"<br>5.1. Lỗi database → Thông báo lỗi hệ thống |
| **Tiền sự kiện** | Người dùng chưa có tài khoản (đăng ký) hoặc đã có tài khoản (đăng nhập) |
| **Hậu sự kiện** | Tài khoản được tạo/đăng nhập thành công, session được thiết lập |

---

### UC-02: Xem và tìm kiếm sản phẩm

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Xem và tìm kiếm sản phẩm |
| **Tác nhân** | Người dùng |
| **Dòng sự kiện chính** | 1. Người dùng truy cập trang sản phẩm hoặc tìm kiếm<br>2. Hệ thống hiển thị danh sách sản phẩm (có phân trang)<br>3. Người dùng có thể lọc theo danh mục, giá, tìm kiếm từ khóa<br>4. Click vào sản phẩm để xem chi tiết<br>5. Hệ thống hiển thị: tên, giá, mô tả, ảnh, size, đánh giá<br>6. Hiển thị sản phẩm liên quan (cùng danh mục)<br>7. Hiển thị AI chatbox để tư vấn |
| **Dòng sự kiện ngoại lệ** | 2.1. Không có sản phẩm → Hiển thị "Không có sản phẩm"<br>4.1. Sản phẩm không tồn tại → Thông báo lỗi |
| **Tiền sự kiện** | Database có sản phẩm |
| **Hậu sự kiện** | Người dùng xem được thông tin sản phẩm đầy đủ |

---

### UC-03: Quản lý giỏ hàng

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý giỏ hàng |
| **Tác nhân** | Người dùng |
| **Dòng sự kiện chính** | **Thêm vào giỏ:**<br>1. Chọn size và số lượng<br>2. Nhấn "Thêm vào giỏ hàng"<br>3. Hệ thống kiểm tra sản phẩm+size đã có trong giỏ chưa<br>4. Nếu có: cộng thêm số lượng; Nếu chưa: thêm mới<br>5. Lưu vào session: [{'product_id', 'size_id', 'quantity'}]<br><br>**Xem/Cập nhật giỏ:**<br>1. Hiển thị danh sách sản phẩm, ảnh, size, số lượng, giá<br>2. Người dùng có thể thay đổi số lượng hoặc xóa sản phẩm<br>3. Cập nhật session và tính lại tổng tiền |
| **Dòng sự kiện ngoại lệ** | 1.1. Chưa chọn size → Thông báo lỗi<br>3.1. Lỗi session → Thông báo lỗi |
| **Tiền sự kiện** | Sản phẩm tồn tại |
| **Hậu sự kiện** | Giỏ hàng được cập nhật trong session |

---

### UC-04: Đặt hàng và áp dụng voucher

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Đặt hàng và áp dụng voucher |
| **Tác nhân** | Người dùng |
| **Dòng sự kiện chính** | 1. Người dùng vào trang checkout<br>2. Hệ thống hiển thị sản phẩm trong giỏ, tổng tiền<br>3. Nhập thông tin: họ tên, địa chỉ, SĐT, ghi chú<br>4. Chọn phương thức thanh toán (COD/Bank/VNPay)<br>5. Nhập mã voucher (tùy chọn)<br>6. Hệ thống kiểm tra voucher: thời hạn, giá trị tối thiểu, số lần dùng<br>7. Tính giảm giá (percentage hoặc fixed)<br>8. Nhấn "Đặt hàng"<br>9. Tạo order với status='pending', lưu order_items<br>10. Tăng used_count của voucher<br>11. Xóa giỏ hàng<br>12. Chuyển hướng theo phương thức thanh toán |
| **Dòng sự kiện ngoại lệ** | 3.1. Thiếu thông tin → Thông báo lỗi<br>6.1. Voucher không hợp lệ → Không áp dụng giảm giá<br>9.1. Lỗi tạo order → Thông báo lỗi |
| **Tiền sự kiện** | Giỏ hàng có sản phẩm |
| **Hậu sự kiện** | Đơn hàng được tạo, giỏ hàng xóa |

---

### UC-05: Đánh giá sản phẩm

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Đánh giá sản phẩm |
| **Tác nhân** | Người dùng |
| **Dòng sự kiện chính** | 1. Người dùng đã đăng nhập, xem sản phẩm<br>2. Chọn số sao (1-5)<br>3. Nhập nội dung đánh giá<br>4. Nhấn "Gửi đánh giá"<br>5. Hệ thống lưu review: user_id, product_id, rating, comment<br>6. Tạo thông báo cho admin<br>7. Hiển thị đánh giá mới trên trang |
| **Dòng sự kiện ngoại lệ** | 1.1. Chưa đăng nhập → Thông báo lỗi<br>2.1. Rating không hợp lệ → Thông báo lỗi |
| **Tiền sự kiện** | Người dùng đã đăng nhập |
| **Hậu sự kiện** | Đánh giá được lưu và hiển thị |

---

### UC-06: Tương tác với AI Chatbot

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Tương tác với AI Chatbot |
| **Tác nhân** | Người dùng |
| **Dòng sự kiện chính** | 1. Người dùng nhập câu hỏi vào chatbox<br>2. Gửi request đến AI server: message, user_id, session_id, product_id<br>3. AI phân tích ý định (greeting, recommend, size, voucher, budget)<br>4. Trích xuất thông tin: ngân sách, từ khóa, số đo<br>5. Lấy context: sản phẩm, voucher, sizes<br>6. Xử lý logic tư vấn<br>7. Tạo câu trả lời với link sản phẩm, voucher<br>8. Lưu lịch sử hội thoại<br>9. Trả về response, hiển thị trong chatbox |
| **Dòng sự kiện ngoại lệ** | 2.1. Lỗi kết nối AI → Thông báo lỗi<br>6.1. Không tìm thấy sản phẩm → Thông báo |
| **Tiền sự kiện** | AI server đang chạy |
| **Hậu sự kiện** | Người dùng nhận câu trả lời, lịch sử được lưu |

---

### UC-07: Quản lý sản phẩm

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý sản phẩm |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | **Thêm:**<br>1. Nhấn "+ Thêm sản phẩm"<br>2. Nhập: tên, giá, số lượng, danh mục, mô tả<br>3. Upload ảnh<br>4. Đánh dấu "Nổi bật" (tùy chọn)<br>5. Lưu vào database: products, product_images<br><br>**Sửa:**<br>1. Chọn sản phẩm, nhấn "Sửa"<br>2. Cập nhật thông tin<br>3. Lưu thay đổi<br><br>**Xóa:**<br>1. Chọn sản phẩm, nhấn "Xóa"<br>2. Xóa ảnh vật lý và records trong database |
| **Dòng sự kiện ngoại lệ** | 2.1. Thiếu thông tin → Thông báo lỗi<br>3.1. Lỗi upload → Thông báo lỗi |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Sản phẩm được thêm/sửa/xóa |

---

### UC-08: Quản lý danh mục

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý danh mục |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem danh sách danh mục<br>2. Thêm mới: nhập tên, mô tả, lưu vào categories<br>3. Sửa: cập nhật thông tin danh mục<br>4. Xóa: xóa danh mục (kiểm tra không có sản phẩm) |
| **Dòng sự kiện ngoại lệ** | 4.1. Danh mục có sản phẩm → Không cho xóa |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Danh mục được quản lý |

---

### UC-09: Quản lý đơn hàng

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý đơn hàng |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem danh sách đơn hàng: mã, khách hàng, tổng tiền, trạng thái<br>2. Xem chi tiết: sản phẩm, số lượng, địa chỉ<br>3. Cập nhật trạng thái: pending→processing→shipped→delivered<br>4. Tạo thông báo cho khách hàng<br>5. Xem lịch sử đơn hàng |
| **Dòng sự kiện ngoại lệ** | 3.1. Lỗi cập nhật → Thông báo lỗi |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Trạng thái đơn hàng được cập nhật |

---

### UC-10: Quản lý người dùng

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý người dùng |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem danh sách người dùng<br>2. Thêm mới: nhập thông tin, chọn role (admin/customer)<br>3. Sửa: cập nhật thông tin, đổi role<br>4. Xóa: xóa người dùng |
| **Dòng sự kiện ngoại lệ** | 2.1. Email trùng → Thông báo lỗi |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Người dùng được quản lý |

---

### UC-11: Quản lý voucher

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý voucher |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem danh sách voucher<br>2. Thêm: mã, loại (percentage/fixed), giá trị, min_order, max_discount, số lần dùng, thời hạn<br>3. Sửa: cập nhật thông tin<br>4. Xóa: xóa voucher |
| **Dòng sự kiện ngoại lệ** | 2.1. Mã trùng → Thông báo lỗi<br>2.2. Ngày không hợp lệ → Thông báo lỗi |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Voucher được quản lý |

---

### UC-12: Quản lý đánh giá

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý đánh giá |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem danh sách đánh giá: sản phẩm, người dùng, rating, nội dung<br>2. Duyệt/Ẩn đánh giá không phù hợp |
| **Dòng sự kiện ngoại lệ** | - |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Đánh giá được kiểm duyệt |

---

### UC-13: Quản lý nội dung (banner, khuyến mãi)

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý nội dung |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Quản lý banner: thêm/sửa/xóa ảnh banner, tiêu đề, link, vị trí<br>2. Quản lý banner khuyến mãi: thêm/sửa/xóa promo banner<br>3. Upload ảnh, set thứ tự hiển thị |
| **Dòng sự kiện ngoại lệ** | 3.1. Lỗi upload → Thông báo lỗi |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Nội dung được cập nhật trên trang chủ |

---

### UC-14: Quản lý dữ liệu AI Training

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Quản lý dữ liệu AI Training |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem danh sách training data: source, text, label<br>2. Thêm entry mới: chọn source (conversation/review/manual), nhập text, label<br>3. Xóa entry không cần thiết<br>4. Dữ liệu được dùng để cải thiện AI |
| **Dòng sự kiện ngoại lệ** | 2.1. Thiếu thông tin → Thông báo lỗi |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Dữ liệu training được quản lý |

---

### UC-15: Xem báo cáo và thống kê

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Xem báo cáo và thống kê |
| **Tác nhân** | Quản trị viên |
| **Dòng sự kiện chính** | 1. Xem dashboard: tổng doanh thu, đơn hàng, sản phẩm, người dùng<br>2. Xem báo cáo doanh thu theo thời gian<br>3. Xem sản phẩm bán chạy<br>4. Xem thông báo hệ thống |
| **Dòng sự kiện ngoại lệ** | - |
| **Tiền sự kiện** | Admin đã đăng nhập |
| **Hậu sự kiện** | Admin nắm được tình hình kinh doanh |

---

### UC-16: Phân tích ý định và xử lý hội thoại

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Phân tích ý định và xử lý hội thoại |
| **Tác nhân** | Hệ thống AI |
| **Dòng sự kiện chính** | 1. Nhận message từ người dùng<br>2. Chuẩn hóa text (lowercase, loại dấu)<br>3. Kiểm tra pattern: chào hỏi, hỏi size, gợi ý, voucher, ngân sách<br>4. Xác định intent: greeting, ask_size, ask_recommend, ask_voucher, ask_budget, other<br>5. Trả về intent để xử lý tiếp |
| **Dòng sự kiện ngoại lệ** | 4.1. Không khớp pattern → intent='other' |
| **Tiền sự kiện** | Nhận được message |
| **Hậu sự kiện** | Intent được xác định |

---

### UC-17: Tư vấn sản phẩm theo ngân sách

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Tư vấn sản phẩm theo ngân sách |
| **Tác nhân** | Hệ thống AI |
| **Dòng sự kiện chính** | 1. Trích xuất ngân sách từ text (200k, 2tr, 200.000)<br>2. Chuyển đổi sang VND<br>3. Trích xuất từ khóa (áo, quần, váy)<br>4. Trích xuất giới tính (nam, nữ, unisex)<br>5. Query database: price <= budget, tên chứa từ khóa, phù hợp giới tính<br>6. Lấy top 3-5 sản phẩm<br>7. Tạo câu trả lời với link, giá, voucher |
| **Dòng sự kiện ngoại lệ** | 1.1. Không trích xuất được → Hỏi lại<br>6.1. Không có sản phẩm → Gợi ý gần nhất |
| **Tiền sự kiện** | Database có sản phẩm |
| **Hậu sự kiện** | Người dùng nhận gợi ý sản phẩm |

---

### UC-18: Tư vấn size dựa trên số đo

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Tư vấn size dựa trên số đo |
| **Tác nhân** | Hệ thống AI |
| **Dòng sự kiện chính** | 1. Trích xuất chiều cao (170cm, 1m7)<br>2. Trích xuất cân nặng (60kg)<br>3. Chuẩn hóa về cm và kg<br>4. Lấy size chart của sản phẩm (nếu có)<br>5. Áp dụng rule: <160cm→S, 160-170→M, 170-180→L, >180→XL<br>6. Điều chỉnh theo BMI<br>7. Tạo câu trả lời với size gợi ý và lý do |
| **Dòng sự kiện ngoại lệ** | 1.1. Không trích xuất được → Hỏi lại<br>5.1. Số đo ngoài phạm vi → Gợi ý gần nhất |
| **Tiền sự kiện** | Người dùng cung cấp số đo |
| **Hậu sự kiện** | Người dùng nhận gợi ý size |

---

### UC-19: Quản lý lịch sử hội thoại

| Usecase | Nội dung |
|---------|----------|
| **Tén** | Quản lý lịch sử hội thoại |
| **Tác nhân** | Hệ thống AI |
| **Dòng sự kiện chính** | 1. Sau mỗi hội thoại, lưu vào database: user_id, session_id, direction (user/ai), message, intent, metadata<br>2. Dữ liệu dùng để phân tích và cải thiện AI<br>3. Admin có thể xem lịch sử để hiểu nhu cầu khách hàng |
| **Dòng sự kiện ngoại lệ** | 1.1. Lỗi lưu → Vẫn trả response |
| **Tiền sự kiện** | Có hội thoại |
| **Hậu sự kiện** | Lịch sử được lưu |

---

### UC-20: Thanh toán qua VNPay

| Usecase | Nội dung |
|---------|----------|
| **Tên** | Thanh toán qua VNPay |
| **Tác nhân** | Người dùng, Hệ thống VNPay |
| **Dòng sự kiện chính** | 1. Người dùng chọn thanh toán VNPay<br>2. Hệ thống tạo order với status='pending'<br>3. Lấy config từ .env: TMN_CODE, HASH_SECRET, RETURN_URL<br>4. Tạo tham số: vnp_Amount, vnp_OrderInfo, vnp_TxnRef (order_id), vnp_CreateDate<br>5. Sắp xếp tham số theo alphabet<br>6. Tạo chữ ký HMAC SHA512<br>7. Tạo URL thanh toán VNPay<br>8. Chuyển hướng đến VNPay<br>9. Người dùng thanh toán trên VNPay<br>10. VNPay callback về RETURN_URL<br>11. Xác thực chữ ký<br>12. Kiểm tra vnp_ResponseCode<br>13. Nếu 00: cập nhật status='paid'; Nếu lỗi: status='failed'<br>14. Hiển thị kết quả |
| **Dòng sự kiện ngoại lệ** | 3.1. Thiếu config → Thông báo lỗi<br>11.1. Chữ ký không hợp lệ → Thông báo giao dịch không an toàn<br>12.1. Mã lỗi khác 00 → Hiển thị lỗi tương ứng |
| **Tiền sự kiện** | Đơn hàng đã tạo, VNPay được cấu hình |
| **Hậu sự kiện** | Giao dịch hoàn tất, trạng thái đơn hàng cập nhật |
