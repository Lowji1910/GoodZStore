# 📖 HƯỚNG DẪN CHẠY HỆ THỐNG AI CHATBOT - GOODZSTORE

## 📋 Mục lục

1. [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
2. [Cài đặt từng bước](#cài-đặt-từng-bước)
3. [Chạy hệ thống](#chạy-hệ-thống)
4. [Kiểm tra và test](#kiểm-tra-và-test)
5. [Sử dụng tính năng](#sử-dụng-tính-năng)
6. [Xử lý lỗi thường gặp](#xử-lý-lỗi-thường-gặp)

---

## 🖥️ Yêu cầu hệ thống

### Phần mềm cần có:

- ✅ **Python 3.9 trở lên** - [Download](https://www.python.org/downloads/)
- ✅ **XAMPP/WAMP** (đã cài MySQL và Apache)
- ✅ **Web browser** (Chrome, Firefox, Edge...)
- ✅ **Text editor** (VS Code, Notepad++...)

### Kiểm tra Python:

Mở **Command Prompt** (CMD) và gõ:

```bash
python --version
```

Phải hiện: `Python 3.9.x` hoặc cao hơn

---

## 🔧 Cài đặt từng bước

### BƯỚC 1: Cài đặt thư viện Python

Mở **Command Prompt** và di chuyển vào thư mục project:

```bash
cd D:\BaiHoc\ĐACN\GoodZStore\ai_server
```

Cài đặt các thư viện cần thiết:

```bash
pip install flask pymysql python-dotenv google-generativeai flask-cors
```

Hoặc dùng file requirements.txt:

```bash
pip install -r requirements.txt
```

**Chờ cài đặt xong** (khoảng 1-2 phút)

### BƯỚC 2: Tạo bảng Database

**Cách 1: Dùng phpMyAdmin**

1. Mở trình duyệt, vào: `http://localhost/phpmyadmin`
2. Đăng nhập (thường không cần password)
3. Chọn database **goodzstore** ở bên trái
4. Click tab **SQL** ở trên
5. Click **Choose File** → Chọn file: `D:\BaiHoc\ĐACN\GoodZStore\migrations\create_ai_tables.sql`
6. Click **Go** (hoặc **Thực hiện**)
7. Thấy thông báo "Query OK" = Thành công ✅

**Cách 2: Dùng Command Line**

```bash
mysql -u root -p goodzstore < D:\BaiHoc\ĐACN\GoodZStore\migrations\create_ai_tables.sql
```

Nhập password MySQL (nếu có), Enter.

### BƯỚC 3: Kiểm tra cấu hình

Mở file `ai_server\.env` bằng Notepad hoặc VS Code:

```env
GOOGLE_API_KEY=AIzaSyCNOMzJsgx1CsBbjOpkfRMo4Lf8_RUCgrM
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=goodzstore
PORT=5000
```

**Chỉnh sửa nếu cần:**

- `DB_PASS=` → Nếu MySQL có password, điền vào đây
- `DB_USER=root` → Nếu dùng user khác, đổi tên
- `GOOGLE_API_KEY=...` → API key đã có sẵn, có thể dùng luôn

**Lưu file** sau khi chỉnh sửa.

---

## ▶️ Chạy hệ thống

### Cách 1: Dùng Batch File (Đơn giản nhất)

1. Vào thư mục `D:\BaiHoc\ĐACN\GoodZStore\ai_server`
2. **Double-click** vào file `start_server.bat`
3. Cửa sổ CMD sẽ mở và chạy server tự động

### Cách 2: Dùng Command Prompt

1. Mở **Command Prompt**
2. Chạy lệnh:

```bash
cd D:\BaiHoc\ĐACN\GoodZStore\ai_server
python app.py
```

### Kết quả khi chạy thành công:

```
 * Serving Flask app 'app'
 * Debug mode: on
WARNING: This is a development server.
 * Running on http://127.0.0.1:5000
Press CTRL+C to quit
```

**✅ Server đã chạy!** Giữ cửa sổ CMD mở, không tắt.

---

## 🧪 Kiểm tra và Test

### Test 1: Kiểm tra server đang chạy

Mở trình duyệt, vào: `http://127.0.0.1:5000`

- Nếu **KHÔNG** thấy lỗi "This site can't be reached" = OK ✅
- Có thể thấy trang trắng hoặc lỗi 404 = Bình thường (vì chưa có route `/`)

### Test 2: Test API bằng script

Mở **Command Prompt MỚI** (giữ cái cũ chạy server):

```bash
cd D:\BaiHoc\ĐACN\GoodZStore\ai_server
python test_api.py
```

Bạn sẽ thấy kết quả test các API endpoints.

### Test 3: Test trên website

1. **Bật XAMPP** (Apache phải đang chạy)
2. Mở trình duyệt, vào: `http://localhost/GoodZStore/Views/Users/product.php?id=1`
3. **Cuộn xuống** tìm phần **"Trợ lý AI - Tư vấn thời trang"**
4. Gõ câu hỏi vào ô chat, ví dụ:
   - "Tôi cao 170cm, nên mặc size nào?"
   - "Có mã giảm giá không?"
   - "Áo này phối với quần gì đẹp?"
5. Click **Gửi**
6. Chờ AI trả lời (3-5 giây)

**Nếu thấy câu trả lời = Thành công! 🎉**

---

## 🎯 Sử dụng tính năng

### 1. Chat với AI trên trang sản phẩm

**Các loại câu hỏi AI có thể trả lời:**

| Loại | Ví dụ câu hỏi |
|------|---------------|
| **Size** | "Tôi cao 165cm, nặng 55kg, size nào vừa?" |
| **Khuyến mãi** | "Có mã giảm giá không?" |
| **Phối đồ** | "Áo này mặc với quần gì đẹp?" |
| **Màu sắc** | "Sản phẩm có màu nào?" |
| **Chất liệu** | "Chất liệu áo này thế nào?" |

**AI sẽ trả về:**
- ✅ Câu trả lời văn bản
- ✅ Gợi ý size (nếu hỏi về size)
- ✅ Danh sách sản phẩm liên quan
- ✅ Mã giảm giá đang có

### 2. Quản lý Training Data (Admin)

**Bước 1:** Đăng nhập admin

- Vào: `http://localhost/GoodZStore/Views/Users/login.php`
- Đăng nhập bằng tài khoản admin

**Bước 2:** Vào trang quản lý AI

- Vào: `http://localhost/GoodZStore/Views/Admins/admin_ai_training.php`
- Hoặc click **🤖 AI Training** ở sidebar admin

**Bước 3:** Xem hội thoại

- Tab **"Hội thoại"** hiển thị tất cả tin nhắn người dùng
- Xem nội dung, thời gian, session ID

**Bước 4:** Thêm vào Training Data

1. Click **"Thêm vào Training"** ở hội thoại muốn lưu
2. Chọn **Label** phù hợp:
   - `recommend` - Gợi ý sản phẩm
   - `ask_size` - Hỏi về size
   - `promo` - Khuyến mãi
   - `general` - Câu hỏi chung
   - `style_advice` - Tư vấn phối đồ
3. Click **Lưu**

**Bước 5:** Quản lý Training Data

- Tab **"Dữ liệu huấn luyện"** hiển thị data đã lưu
- Có thể xóa data không phù hợp

---

## ❌ Xử lý lỗi thường gặp

### Lỗi 1: "python is not recognized"

**Nguyên nhân:** Python chưa cài hoặc chưa thêm vào PATH

**Giải pháp:**
1. Cài Python từ https://www.python.org/downloads/
2. **Quan trọng:** Tick ✅ "Add Python to PATH" khi cài
3. Restart CMD sau khi cài

### Lỗi 2: "ModuleNotFoundError: No module named 'flask'"

**Nguyên nhân:** Chưa cài thư viện

**Giải pháp:**
```bash
pip install flask pymysql python-dotenv google-generativeai flask-cors
```

### Lỗi 3: "Can't connect to MySQL server"

**Nguyên nhân:** MySQL chưa chạy hoặc cấu hình sai

**Giải pháp:**
1. Mở **XAMPP Control Panel**
2. Click **Start** ở MySQL (phải hiện chữ xanh)
3. Kiểm tra file `.env`:
   - `DB_HOST=127.0.0.1` (hoặc `localhost`)
   - `DB_USER=root`
   - `DB_PASS=` (điền password nếu có)

### Lỗi 4: "Table 'goodzstore.ai_conversations' doesn't exist"

**Nguyên nhân:** Chưa chạy file SQL tạo bảng

**Giải pháp:**
1. Vào phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `goodzstore`
3. Tab **SQL** → Import file `create_ai_tables.sql`
4. Click **Go**

### Lỗi 5: "CORS policy blocked"

**Nguyên nhân:** Trình duyệt chặn request từ domain khác

**Giải pháp:**
- Server đã cài `flask-cors`, restart server:
  1. Tắt server (Ctrl+C trong CMD)
  2. Chạy lại: `python app.py`

### Lỗi 6: "API key not valid" (Gemini)

**Nguyên nhân:** API key sai hoặc hết quota

**Giải pháp:**
1. Kiểm tra API key trong file `.env`
2. Tạo API key mới tại: https://makersuite.google.com/app/apikey
3. Copy key mới vào `.env`
4. Restart server

### Lỗi 7: Chatbox không hiện trên trang product

**Nguyên nhân:** File product.php chưa cập nhật

**Giải pháp:**
- File đã được cập nhật tự động
- Refresh trang (Ctrl+F5)
- Xóa cache trình duyệt

### Lỗi 8: "Address already in use" (Port 5000)

**Nguyên nhân:** Port 5000 đang được dùng bởi app khác

**Giải pháp:**

**Cách 1:** Tắt app đang dùng port 5000

```bash
# Tìm process
netstat -ano | findstr :5000

# Kill process (thay <PID> bằng số thực tế)
taskkill /PID <PID> /F
```

**Cách 2:** Đổi port trong `.env`

```env
PORT=5001
```

Và cập nhật URL trong `product.php`:
```javascript
fetch('http://127.0.0.1:5001/api/chat', ...)
```

---

## 📊 Kiểm tra Database

Vào phpMyAdmin, chạy các query sau để kiểm tra:

### Xem các bảng AI:

```sql
SHOW TABLES LIKE 'ai_%';
```

Phải thấy:
- `ai_conversations`
- `ai_training_data`

### Xem hội thoại đã lưu:

```sql
SELECT * FROM ai_conversations ORDER BY created_at DESC LIMIT 10;
```

### Xem training data:

```sql
SELECT * FROM ai_training_data ORDER BY created_at DESC LIMIT 10;
```

### Thống kê theo label:

```sql
SELECT label, COUNT(*) as total FROM ai_training_data GROUP BY label;
```

---

## 🎓 Cấu trúc Project

```
GoodZStore/
│
├── ai_server/                    ← Thư mục AI Server
│   ├── app.py                    ← File chính Flask server
│   ├── .env                      ← Cấu hình (API key, database)
│   ├── requirements.txt          ← Danh sách thư viện Python
│   ├── start_server.bat          ← Script chạy nhanh (Windows)
│   ├── test_api.py               ← Script test API
│   ├── README.md                 ← Tài liệu chi tiết (English)
│   ├── DEPLOYMENT.md             ← Hướng dẫn deploy production
│   └── QUICKSTART.md             ← Hướng dẫn nhanh
│
├── migrations/
│   └── create_ai_tables.sql      ← SQL tạo bảng database
│
├── Views/
│   ├── Users/
│   │   └── product.php           ← Trang sản phẩm (có chatbox)
│   │
│   └── Admins/
│       ├── admin_sidebar.php     ← Sidebar admin (đã thêm link AI)
│       └── admin_ai_training.php ← Trang quản lý training data
│
└── HUONG_DAN_CHAY_AI.md          ← File này (Hướng dẫn tiếng Việt)
```

---

## 📝 Checklist hoàn thành

Đánh dấu ✅ khi hoàn thành:

- [ ] Python đã cài đặt (version 3.9+)
- [ ] Thư viện Python đã cài (flask, pymysql, etc.)
- [ ] XAMPP/MySQL đang chạy
- [ ] Database tables đã tạo (ai_conversations, ai_training_data)
- [ ] File .env đã cấu hình đúng
- [ ] Flask server chạy thành công (port 5000)
- [ ] Test API thành công (test_api.py)
- [ ] Chatbox hiện trên trang product
- [ ] Chat với AI thành công
- [ ] Admin panel truy cập được
- [ ] Có thể thêm training data

---

## 🎯 Demo Flow hoàn chỉnh

### Kịch bản test đầy đủ:

1. **Khởi động server**
   ```bash
   cd D:\BaiHoc\ĐACN\GoodZStore\ai_server
   python app.py
   ```

2. **Mở trang sản phẩm**
   - URL: `http://localhost/GoodZStore/Views/Users/product.php?id=1`

3. **Chat với AI**
   - Gõ: "Tôi cao 170cm, nên mặc size nào?"
   - Xem AI trả lời + gợi ý size

4. **Hỏi về khuyến mãi**
   - Gõ: "Có mã giảm giá không?"
   - Xem AI liệt kê vouchers

5. **Kiểm tra database**
   - Vào phpMyAdmin
   - Xem bảng `ai_conversations`
   - Thấy 2 tin nhắn vừa chat

6. **Vào admin panel**
   - Login admin
   - Vào: AI Training
   - Thấy 2 hội thoại vừa tạo

7. **Thêm vào training**
   - Click "Thêm vào Training"
   - Chọn label: `ask_size`
   - Lưu

8. **Kiểm tra training data**
   - Tab "Dữ liệu huấn luyện"
   - Thấy data vừa thêm

**✅ Hoàn thành!**

---

## 💡 Tips & Tricks

### 1. Chạy server tự động khi khởi động Windows

Tạo shortcut của `start_server.bat` vào thư mục Startup:
```
C:\Users\<YourName>\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup
```

### 2. Xem logs real-time

Khi server chạy, mọi request sẽ hiện trong CMD:
```
127.0.0.1 - - [27/Oct/2024 15:30:45] "POST /api/chat HTTP/1.1" 200 -
```

### 3. Test nhanh API không cần browser

Dùng PowerShell:
```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:5000/api/chat" -Method POST -ContentType "application/json" -Body '{"message":"test","user_id":null,"session_id":"test-001","metadata":{}}'
```

### 4. Backup training data

Export từ phpMyAdmin:
1. Chọn bảng `ai_training_data`
2. Tab **Export**
3. Format: SQL
4. Click **Go**

---

## 📞 Hỗ trợ

Nếu gặp vấn đề không giải quyết được:

1. **Kiểm tra logs:**
   - Terminal Flask server
   - Browser Console (F12 → Console)
   - phpMyAdmin → SQL logs

2. **Đọc tài liệu:**
   - `README.md` - Chi tiết về API
   - `DEPLOYMENT.md` - Deploy production
   - `QUICKSTART.md` - Hướng dẫn nhanh

3. **Common issues:**
   - 90% lỗi do: chưa cài thư viện, MySQL chưa chạy, hoặc .env sai
   - Restart server sau mỗi lần sửa code
   - Clear browser cache nếu frontend không update

---

## ✨ Tính năng đã hoàn thành

- ✅ Flask AI Server với Gemini API
- ✅ Chatbox tích hợp vào trang sản phẩm
- ✅ Gợi ý size dựa trên chiều cao
- ✅ Gợi ý sản phẩm liên quan
- ✅ Hiển thị vouchers đang active
- ✅ Lưu lịch sử hội thoại
- ✅ Admin panel quản lý training data
- ✅ Phân loại data theo label
- ✅ Database schema hoàn chỉnh
- ✅ API documentation
- ✅ Test scripts
- ✅ Deployment guide

---

**Chúc bạn thành công! 🚀**

*Nếu có câu hỏi, hãy đọc kỹ phần Troubleshooting hoặc kiểm tra logs.*
