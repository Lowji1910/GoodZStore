# GoodZStore E-commerce Platform

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.9+-3776AB?style=flat&logo=python&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)

GoodZStore là một nền tảng thương mại điện tử hiện đại chuyên về thời trang, được xây dựng với PHP, MySQL và tích hợp AI chatbot thông minh. Hệ thống cung cấp trải nghiệm mua sắm trực tuyến hoàn chỉnh với các tính năng quản lý sản phẩm, đơn hàng, thanh toán và hỗ trợ khách hàng tự động.

---

## 📋 Mục lục

- [Tính năng chính](#-tính-năng-chính)
- [Kiến trúc hệ thống](#-kiến-trúc-hệ-thống)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [Cài đặt](#-cài-đặt)
- [Cấu hình](#-cấu-hình)
- [Sử dụng](#-sử-dụng)
- [Cấu trúc thư mục](#-cấu-trúc-thư-mục)
- [API Documentation](#-api-documentation)
- [Đóng góp](#-đóng-góp)
- [License](#-license)

---

## 🚀 Tính năng chính

### Người dùng (Customer)
- ✅ **Xác thực & Quản lý tài khoản**
  - Đăng ký/đăng nhập an toàn với mã hóa password
  - Quản lý thông tin cá nhân và lịch sử đơn hàng
  
- 🛍️ **Mua sắm**
  - Duyệt sản phẩm theo danh mục với bộ lọc nâng cao
  - Tìm kiếm thông minh
  - Giỏ hàng động (session + database)
  - Đánh giá và xếp hạng sản phẩm
  
- 💳 **Thanh toán**
  - Thanh toán COD (ship COD)
  - Tích hợp VNPAY (cổng thanh toán trực tuyến)
  - Áp dụng mã giảm giá (voucher)
  
- 🤖 **AI Chatbot**
  - Tư vấn sản phẩm thông minh
  - Gợi ý dựa trên ngân sách và sở thích
  - Hỗ trợ 24/7

- 🔔 **Thông báo thời gian thực**
  - Thông báo đơn hàng thành công
  - Cập nhật trạng thái đơn hàng
  - Đánh dấu đã đọc và giảm số lượng thông báo

### Quản trị viên (Admin)
- 📊 **Dashboard & Báo cáo**
  - Thống kê doanh thu, đơn hàng theo thời gian
  - Biểu đồ động (Bar, Line, Pie, Doughnut)
  - Phân trang và tìm kiếm nâng cao
  
- 📦 **Quản lý sản phẩm**
  - CRUD sản phẩm với nhiều ảnh
  - Quản lý danh mục và kích thước
  - Upload hình ảnh
  
- 🛒 **Quản lý đơn hàng**
  - Xem chi tiết đơn hàng
  - Cập nhật trạng thái (Pending → Processing → Completed/Cancelled)
  - Thông báo tự động đến khách hàng
  
- 👤 **Quản lý người dùng**
  - Phân quyền (Admin/Customer)
  - Quản lý thông tin người dùng
  
- 🎟️ **Quản lý Voucher**
  - Tạo mã giảm giá theo phần trăm hoặc số tiền cố định
  - Thiết lập điều kiện (số tiền tối thiểu, giới hạn sử dụng, thời gian)
  
- ⭐ **Quản lý đánh giá**
  - Duyệt/xóa đánh giá sản phẩm
  
- 🎨 **Quản lý nội dung**
  - Quản lý banner và nội dung trang chủ
  
- 🤖 **AI Training**
  - Huấn luyện chatbot với dữ liệu sản phẩm

---

## 🏗️ Kiến trúc hệ thống

```
┌─────────────────────────────────────────────────────────┐
│                    USER INTERFACE                       │
│         (HTML5, CSS3, Bootstrap 5, JavaScript)          │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                  APPLICATION LAYER                      │
│                      (PHP 7.4+)                         │
├─────────────────────────────────────────────────────────┤
│  • Views/ (Presentation)                                │
│  • Controllers/ (Business Logic)                        │
│  • Models/ (Data Access)                                │
└─────────────────────────────────────────────────────────┘
                           │
          ┌────────────────┼────────────────┐
          ▼                ▼                ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   DATABASE   │  │  AI SERVER   │  │   VNPAY API  │
│ MySQL 8.0+   │  │ Flask/Python │  │   Gateway    │
└──────────────┘  └──────────────┘  └──────────────┘
```

### Luồng dữ liệu chính:
1. **User Request** → PHP Application → MySQL Database
2. **AI Chat** → PHP → Flask AI Server → Google Gemini API → Response
3. **Payment** → PHP → VNPAY Gateway → Callback → Order Update

---

## 🛠️ Công nghệ sử dụng

### Backend
- **PHP 7.4+**: Server-side scripting
- **MySQL 8.0+**: Relational database
- **Python 3.9+**: AI server (Flask framework)

### Frontend
- **HTML5/CSS3**: Markup và styling
- **Bootstrap 5.3**: Responsive framework
- **JavaScript (Vanilla)**: Client-side logic
- **Chart.js**: Data visualization

### Thư viện & API
- **Google Gemini API**: AI chatbot intelligence
- **VNPAY Payment Gateway**: Online payment processing
- **Font Awesome**: Icons
- **Google Fonts**: Typography

### Công cụ phát triển
- **XAMPP**: Development environment
- **phpMyAdmin**: Database management
- **Git**: Version control

---

## 💻 Yêu cầu hệ thống

### Phần mềm cần thiết
- **Windows 10/11** (hoặc macOS/Linux với điều chỉnh path)
- **XAMPP** (bao gồm Apache 2.4+ và MySQL 8.0+)
- **PHP 7.4+** (đi kèm XAMPP)
- **Python 3.9+** (cho AI server)
- **Trình duyệt hiện đại** (Chrome, Firefox, Edge)

### Dung lượng
- **Ổ cứng**: Tối thiểu 500MB (không bao gồm uploads)
- **RAM**: Tối thiểu 2GB khả dụng

---

## 📥 Cài đặt

### Bước 1: Clone hoặc tải mã nguồn

```bash
# Clone từ GitHub
git clone https://github.com/your-username/GoodZStore.git

# Di chuyển vào thư mục htdocs của XAMPP
cd C:\xampp\htdocs\
```

Hoặc tải ZIP và giải nén vào `C:\xampp\htdocs\GoodZStore`

### Bước 2: Khởi động XAMPP

1. Mở **XAMPP Control Panel**
2. Click **Start** cho **Apache** và **MySQL**
3. Đợi đến khi status hiển thị màu xanh

### Bước 3: Tạo cơ sở dữ liệu

#### Cách 1: Sử dụng phpMyAdmin (Khuyến nghị)

1. Truy cập `http://localhost/phpmyadmin`
2. Click **New** ở sidebar trái
3. Đặt tên database: `goodzstore`
4. Chọn **Collation**: `utf8mb4_general_ci`
5. Click **Create**
6. Chọn database `goodzstore` vừa tạo
7. Click tab **Import**
8. Click **Choose File** và chọn `migrations/goodzstore.sql`
9. Click **Go** để import

#### Cách 2: Sử dụng MySQL CLI

```powershell
cd C:\xampp\htdocs\GoodZStore
& C:\xampp\mysql\bin\mysql.exe -u root goodzstore < migrations\goodzstore.sql
```

### Bước 4: Cấu hình môi trường

1. Tạo file `.env` trong thư mục gốc:

```bash
cp .env.example .env
```

2. Chỉnh sửa `.env` với thông tin của bạn:

```env
# Database Configuration
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=goodzstore

# VNPAY Configuration
VNPAY_TMN_CODE=your_vnpay_tmn_code_here
VNPAY_HASH_SECRET=your_vnpay_hash_secret_here
VNPAY_RETURN_URL=http://localhost/GoodZStore/Views/Users/vnpay_return.php
VNPAY_BASE_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html

# Google Gemini API
GOOGLE_API_KEY=your_google_api_key_here

# AI Server
PORT=5000
```

> **Lưu ý**: File `.env` chứa thông tin nhạy cảm và đã được thêm vào `.gitignore`. Không commit file này lên Git.

### Bước 5: Cài đặt AI Server

```powershell
# Di chuyển vào thư mục ai_server
cd C:\xampp\htdocs\GoodZStore\ai_server

# Tạo virtual environment
python -m venv venv

# Kích hoạt virtual environment
.\venv\Scripts\activate

# Cài đặt dependencies
pip install -r requirements.txt

# Khởi động server
python app.py
```

**Hoặc sử dụng script tự động (Windows):**

```powershell
cd C:\xampp\htdocs\GoodZStore\ai_server
.\start_server.bat
```

AI Server sẽ chạy tại `http://127.0.0.1:5000`

---

## ⚙️ Cấu hình

### Cấu hình Database (Models/db.php)

Nếu không sử dụng `.env`, có thể chỉnh sửa trực tiếp:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "goodzstore";
```

### Cấu hình VNPAY

Để kích hoạt thanh toán VNPAY:
1. Đăng ký tài khoản tại [VNPAY Sandbox](https://sandbox.vnpayment.vn/)
2. Lấy `TMN_CODE` và `HASH_SECRET`
3. Cập nhật vào file `.env`

### Cấu hình Google Gemini API

1. Truy cập [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Tạo API key
3. Thêm vào `.env` tại `GOOGLE_API_KEY`

---

## 🎮 Sử dụng

### Truy cập Website

**Trang người dùng:**
```
http://localhost/GoodZStore/Views/Users/index.php
```

**Trang quản trị:**
```
http://localhost/GoodZStore/Views/Admins/admin_dashboard.php
```

### Tài khoản mặc định

Sau khi import database, bạn có thể sử dụng:

**Admin:**
- Email: `admin@goodzstore.com`
- Password: `admin123`

**Customer:**
- Đăng ký tài khoản mới hoặc sử dụng tài khoản test (nếu có trong SQL)

### Sử dụng AI Chatbot

1. Đảm bảo AI Server đang chạy
2. Truy cập trang người dùng
3. Click vào icon chatbot ở góc dưới bên phải
4. Gõ câu hỏi hoặc yêu cầu tư vấn sản phẩm

**Ví dụ:**
- "Tôi muốn tìm áo thun nam giá dưới 200k"
- "Gợi ý cho tôi outfit đi dự tiệc"
- "Sản phẩm nào đang giảm giá?"

---

## 📁 Cấu trúc thư mục

```
GoodZStore/
├── ai_server/              # AI Chatbot (Flask/Python)
│   ├── app.py             # Main Flask application
│   ├── requirements.txt   # Python dependencies
│   ├── start_server.bat   # Windows startup script
│   └── venv/              # Virtual environment (gitignored)
│
├── Controllers/           # Business logic layer
│   └── ProductController.php
│
├── Models/                # Data access layer
│   ├── db.php            # Database connection
│   ├── config.php        # Environment config loader
│   ├── cart_functions.php
│   ├── notifications.php
│   └── vnpay_helper.php  # VNPAY integration
│
├── Views/                 # Presentation layer
│   ├── Admins/           # Admin panel
│   │   ├── admin_dashboard.php
│   │   ├── admin_products.php
│   │   ├── admin_orders.php
│   │   ├── admin_reports.php
│   │   └── ...
│   ├── Users/            # Customer-facing pages
│   │   ├── index.php
│   │   ├── products.php
│   │   ├── product.php
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   ├── auth.php
│   │   └── ...
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   ├── header.php        # Global header
│   └── footer.php        # Global footer
│
├── migrations/            # Database migrations
│   └── goodzstore.sql    # Full database schema + sample data
│
├── uploads/               # User uploaded files (gitignored)
│   └── .gitkeep
│
├── public/                # Static assets
│
├── .env.example          # Environment config template
├── .gitignore            # Git ignore rules
├── admin_tool.php        # Admin utility tool
├── README.md             # This file
└── use_cases_specification.md  # Use case documentation
```

---

## 📡 API Documentation

### AI Chatbot API

**Endpoint:** `POST http://127.0.0.1:5000/api/chat`

**Request:**
```json
{
  "message": "Tôi muốn tìm áo thun nam"
}
```

**Response:**
```json
{
  "reply": "Chúng tôi có nhiều mẫu áo thun nam đẹp. Bạn có thể xem tại...",
  "products": [...]
}
```

### Notifications API

**Endpoint:** `GET /Views/Users/notifications_api.php`

**Response:**
```json
{
  "unread": 3,
  "items": [
    {
      "id": 1,
      "type": "Đơn hàng",
      "message": "Đơn hàng #123 đã được cập nhật",
      "link": "/Views/Users/orders.php",
      "is_read": 0,
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

## 🐛 Xử lý sự cố

### Lỗi thường gặp

#### 1. "Headers already sent"
**Nguyên nhân:** Output được gửi trước khi gọi `session_start()` hoặc `header()`

**Giải pháp:**
- Đảm bảo không có khoảng trắng/newline trước tag `<?php`
- Thêm `ob_start()` ở đầu file
- Kiểm tra encoding file (phải là UTF-8 without BOM)

#### 2. Không kết nối được Database
**Giải pháp:**
- Kiểm tra MySQL đang chạy trong XAMPP
- Xác nhận tên database là `goodzstore`
- Kiểm tra username/password trong `Models/db.php` hoặc `.env`

#### 3. AI Server không chạy
**Giải pháp:**
- Kiểm tra Python đã cài đặt: `python --version`
- Kiểm tra virtual environment đã activate
- Kiểm tra `.env` có `GOOGLE_API_KEY`
- Xem log trong console để biết lỗi cụ thể

#### 4. Lỗi upload ảnh
**Giải pháp:**
- Kiểm tra thư mục `uploads/` tồn tại và có quyền ghi
- Trên Windows, thư mục cần có quyền Full Control cho user hiện tại

#### 5. VNPAY thanh toán không hoạt động
**Giải pháp:**
- Kiểm tra `VNPAY_TMN_CODE` và `VNPAY_HASH_SECRET` trong `.env`
- Đảm bảo sử dụng Sandbox URL nếu đang test
- Kiểm tra `VNPAY_RETURN_URL` phải khớp với URL được đăng ký

---

## 🤝 Đóng góp

Chúng tôi hoan nghênh mọi đóng góp! Để đóng góp:

1. Fork repository
2. Tạo branch mới (`git checkout -b feature/AmazingFeature`)
3. Commit thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. Push lên branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

### Quy tắc code

- Sử dụng 4 spaces cho indentation (PHP)
- Đặt tên biến rõ ràng, có ý nghĩa
- Comment code cho các logic phức tạp
- Tuân thủ PSR-12 coding standard (PHP)

---

## 📝 License

Dự án này được phát hành dưới **MIT License**. Xem file [LICENSE](LICENSE) để biết thêm chi tiết.

---

## 👥 Tác giả

- **Your Name** - *Initial work* - [GitHub](https://github.com/your-username)

---

## 🙏 Lời cảm ơn

- [Bootstrap](https://getbootstrap.com/) - UI Framework
- [Chart.js](https://www.chartjs.org/) - Data visualization
- [Google Gemini](https://ai.google.dev/) - AI capabilities
- [VNPAY](https://vnpay.vn/) - Payment gateway
- [Font Awesome](https://fontawesome.com/) - Icons

---

## 📧 Liên hệ

Nếu bạn có câu hỏi hoặc cần hỗ trợ, vui lòng:
- Mở [Issue](https://github.com/your-username/GoodZStore/issues) trên GitHub
- Email: your.email@example.com

---

<div align="center">
  
**Được phát triển với ❤️ bởi GoodZStore Team**

</div>
