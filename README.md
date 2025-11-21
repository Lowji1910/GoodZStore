# GoodZStore — Hướng dẫn chạy cho người mới

Tài liệu này hướng dẫn cách thiết lập và chạy hệ thống GoodZStore trên máy Windows (sử dụng XAMPP) và khởi động dịch vụ AI (Flask) kèm theo. Mục tiêu: người mới có thể chạy được website cục bộ và server AI để thử tính năng chatbot.

**Tổng quan:**
- Backend chính: PHP (các file trong `Views/`, `Models/`, `Controllers/`)
- Database: MySQL (file SQL trong `migrations/goodzstore.sql`)
- Dịch vụ AI: Python + Flask nằm trong thư mục `ai_server/`
- Tĩnh (JS/CSS): `public/`, uploads: `uploads/`

---

**Yêu cầu trước khi bắt đầu**
- Windows 10/11
- XAMPP (Apache + MySQL)
- PHP (đi kèm XAMPP)
- Python 3.9+ (cho `ai_server`)
- Git (tùy chọn)

---

1) Triển khai mã nguồn vào `htdocs`

- Nếu clone từ GitHub: đặt repository vào `C:\xampp\htdocs\GoodZStore` hoặc copy thư mục vào `htdocs`.

2) Bật Apache và MySQL (XAMPP)

- Mở `XAMPP Control Panel` → Start `Apache` và `MySQL`.

3) Tạo / import cơ sở dữ liệu

- Mở `phpMyAdmin` (http://localhost/phpmyadmin)
	- Tạo database mới tên `goodzstore` (nếu chưa có)
	- Chọn database rồi dùng tab `Import` → upload file `migrations/goodzstore.sql`

- Hoặc dùng MySQL CLI (PowerShell):

```powershell
cd C:\xampp\htdocs\GoodZStore
# Nếu mysql trong PATH, có thể dùng:
# mysql -u root -p goodzstore < migrations\goodzstore.sql
# Hoặc dùng đường dẫn đầy đủ của mysql.exe
& C:\xampp\mysql\bin\mysql.exe -u root goodzstore < migrations\goodzstore.sql
```

Ghi chú: file `Models/db.php` mặc định dùng user `root` và password rỗng phù hợp XAMPP. Nếu bạn đổi thông tin, chỉnh `Models/db.php` cho khớp.

4) Chỉnh cấu hình database (nếu cần)

- Mở `Models/db.php` và cập nhật `$servername`, `$username`, `$password`, `$dbname` nếu khác mặc định.

5) Kiểm tra quyền thư mục upload

- Thư mục `uploads/` cần tồn tại và có quyền ghi để lưu ảnh sản phẩm. Trên Windows thường không cần thay đổi, chỉ ensure folder tồn tại.

---

Chạy website local

- Mở trình duyệt vào:

```
http://localhost/GoodZStore/Views/Users/index.php
```

Nếu bạn muốn truy cập root của dự án, có thể cấu hình VirtualHost trong Apache (tuỳ chọn).

---

AI Server (chatbot)

Thư mục: `ai_server/`

1. Tạo file cấu hình môi trường `.env` trong `ai_server/` với ít nhất các biến sau:

```
GOOGLE_API_KEY=your_google_api_key_here
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=goodzstore
PORT=5000
```

2. Khởi động nhanh bằng script (Windows):

```powershell
cd C:\xampp\htdocs\GoodZStore\ai_server
.\start_server.bat
```

Script sẽ tạo virtualenv (nếu chưa có), cài `requirements.txt` và chạy `app.py` trên `http://127.0.0.1:5000`.

3. Khởi động thủ công (nếu cần) - PowerShell:

```powershell
cd C:\xampp\htdocs\GoodZStore\ai_server
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
python app.py
```

4. Kiểm tra endpoint thử nghiệm (ví dụ):

```powershell
curl -X POST http://127.0.0.1:5000/api/chat -H "Content-Type: application/json" -d '{"message":"Xin chào"}'
```

Lưu ý: `app.py` sử dụng biến `GOOGLE_API_KEY` để gọi Gemini (Google Generative API). Nếu bạn không có key, server vẫn chạy nhưng sẽ dùng fallback logic nội bộ.

---

Kiểm tra nhanh / tests

- Có file test API `ai_server/test_api.py` có thể dùng để thử vài request. Bạn có thể chạy nó trong cùng virtualenv.

---

Vấn đề hay gặp (Troubleshooting)

- Lỗi `Headers already sent` (Header mất):
	- Nguyên nhân: có output (HTML/text/space/newline) trước khi gọi `session_start()` hoặc gửi header. Kiểm tra các file `Views/header.php`, `Views/Users/cart.php`.
	- Cách khắc phục: đảm bảo `session_start()` được gọi trước output; có thể bật output buffering `ob_start()` ở đầu file, hoặc include `header.php` đúng thứ tự.

- Lỗi kết nối DB:
	- Kiểm tra MySQL đang chạy, tên DB và thông tin trong `Models/db.php` khớp.

- AI server không chạy / báo lỗi `.env`:
	- Kiểm tra file `.env` tồn tại trong `ai_server/` và có `GOOGLE_API_KEY` (nếu cần). Xem nội dung `ai_server/start_server.bat` để biết các bước cần thiết.

---

Các lệnh hữu ích (PowerShell)

```powershell
# Start XAMPP services manually (XAMPP GUI preferred)
& 'C:\xampp\xampp_start.exe'

# Import SQL (example)
& C:\xampp\mysql\bin\mysql.exe -u root goodzstore < migrations\goodzstore.sql

# Start AI server (from repo root)
cd ai_server; .\start_server.bat

# Activate venv (manual)
cd ai_server; .\venv\Scripts\activate
```

---

Liên hệ / Ghi chú

- Nếu bạn gặp lỗi cụ thể, gửi log lỗi (Apache/PHP error log, Flask console output) để được trợ giúp nhanh hơn.
- Một số tập tin hướng dẫn trong thư mục `ai_server/` (ví dụ `FIX_GEMINI_ERROR.md`) có thể hữu ích nếu gặp lỗi liên quan API Gemini.

Chúc bạn thành công — nếu muốn, tôi có thể cấu hình VirtualHost Apache hoặc viết script khởi tạo DB tự động.

