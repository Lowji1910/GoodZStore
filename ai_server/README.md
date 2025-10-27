# Flask AI Server - GoodZStore

Hệ thống AI chatbot tư vấn thời trang sử dụng Google Gemini API.

## Tính năng

- ✅ Chatbot tư vấn thời trang bằng tiếng Việt
- ✅ Gợi ý size dựa trên chiều cao/cân nặng
- ✅ Gợi ý sản phẩm liên quan
- ✅ Hiển thị mã giảm giá đang hoạt động
- ✅ Lưu lịch sử hội thoại
- ✅ Thu thập dữ liệu để huấn luyện

## Cài đặt

### 1. Cài đặt dependencies

```bash
cd ai_server
pip install -r requirements.txt
```

### 2. Cấu hình môi trường

Tạo file `.env` trong thư mục `ai_server/`:

```env
GOOGLE_API_KEY=YOUR_GEMINI_API_KEY_HERE
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=your_password
DB_NAME=goodzstore
PORT=5000
```

**Lấy API Key:**
1. Truy cập: https://makersuite.google.com/app/apikey
2. Tạo API key mới
3. Copy và paste vào file `.env`

### 3. Tạo bảng database

Chạy các câu lệnh SQL sau trong database `goodzstore`:

```sql
-- Bảng lưu hội thoại
CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(100) NOT NULL,
    direction ENUM('user', 'bot') NOT NULL,
    intent VARCHAR(50) NULL,
    message TEXT NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng lưu dữ liệu huấn luyện
CREATE TABLE IF NOT EXISTS ai_training_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(50) NOT NULL,
    ref_id INT NULL,
    text TEXT NOT NULL,
    label VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source),
    INDEX idx_label (label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Khởi chạy server

```bash
python app.py
```

Server sẽ chạy tại: `http://127.0.0.1:5000`

## API Endpoints

### 1. Chat Endpoint

**POST** `/api/chat`

Gửi tin nhắn và nhận phản hồi từ AI.

**Request Body:**
```json
{
  "message": "Tôi cao 170cm, nên mặc size nào?",
  "user_id": 123,
  "session_id": "ses-1234567890",
  "metadata": {
    "product_id": 45,
    "height_cm": 170,
    "weight_kg": 65
  }
}
```

**Response:**
```json
{
  "text": "Với chiều cao 170cm, mình gợi ý bạn nên chọn size M...",
  "session_id": "ses-1234567890",
  "size_suggestion": {
    "size": "M",
    "reason": "Gợi ý dựa trên chiều cao 170cm"
  },
  "recommendations": [
    {
      "id": 46,
      "name": "Áo thun basic",
      "slug": "ao-thun-basic",
      "price": 250000,
      "image_url": "product-46.jpg"
    }
  ],
  "vouchers": [
    {
      "code": "SUMMER2024",
      "discount_type": "percentage",
      "discount_value": 20,
      "min_order_amount": 500000
    }
  ]
}
```

### 2. Size Recommendation Endpoint

**POST** `/api/size`

Gợi ý size cho sản phẩm cụ thể.

**Request Body:**
```json
{
  "product_id": 45,
  "user_id": 123,
  "session_id": "ses-1234567890",
  "measurements": {
    "height_cm": 170,
    "weight_kg": 65,
    "size": "M"
  }
}
```

**Response:**
```json
{
  "size": "M",
  "reason": "Gợi ý dựa trên chiều cao 170cm",
  "available_sizes": ["S", "M", "L", "XL"]
}
```

## Tích hợp Frontend

### PHP + JavaScript

Xem file: `Views/Users/product.php` để tham khảo cách tích hợp chatbox.

**Các bước:**
1. Thêm HTML chatbox vào trang
2. Thêm JavaScript để gọi API
3. Xử lý và hiển thị kết quả

## Quản lý Training Data (Admin)

Truy cập: `Views/Admins/admin_ai_training.php`

**Chức năng:**
- Xem tất cả hội thoại
- Chọn hội thoại tốt để làm training data
- Gán label (recommend, ask_size, promo, etc.)
- Quản lý dữ liệu huấn luyện

**Labels:**
- `recommend` - Gợi ý sản phẩm
- `ask_size` - Hỏi về size
- `promo` - Khuyến mãi
- `general` - Câu hỏi chung
- `style_advice` - Tư vấn phối đồ

## Cấu trúc Database

### Bảng `ai_conversations`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Primary key |
| user_id | INT | ID người dùng (nullable) |
| session_id | VARCHAR(100) | ID phiên chat |
| direction | ENUM | 'user' hoặc 'bot' |
| intent | VARCHAR(50) | Ý định (nullable) |
| message | TEXT | Nội dung tin nhắn |
| metadata | JSON | Dữ liệu bổ sung |
| created_at | TIMESTAMP | Thời gian tạo |

### Bảng `ai_training_data`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Primary key |
| source | VARCHAR(50) | Nguồn dữ liệu |
| ref_id | INT | ID tham chiếu (nullable) |
| text | TEXT | Nội dung |
| label | VARCHAR(50) | Nhãn phân loại |
| created_at | TIMESTAMP | Thời gian tạo |

## Chiến lược huấn luyện

### 1. Thu thập dữ liệu
- Lưu tất cả hội thoại vào `ai_conversations`
- Admin chọn hội thoại chất lượng cao
- Gán label phù hợp

### 2. Tiền xử lý
- Chuẩn hóa text (lowercase, remove special chars)
- Tách intent và slots
- Extract entities (product_id, size, price range)

### 3. RAG (Retrieval-Augmented Generation)
- Tạo embeddings cho training data
- Sử dụng `sentence-transformers` hoặc Gemini embeddings
- Lưu index (FAISS/Annoy)
- Khi inference: fetch nearest docs → attach vào prompt

### 4. Đánh giá
- Chia train/val (80/20)
- Metrics:
  - Accuracy (size suggestion)
  - Precision@k (recommendations)
  - User satisfaction (ratings)

## Bảo mật

### ⚠️ Quan trọng

1. **Không commit API key** vào Git
2. **Sử dụng .env** cho sensitive data
3. **Giới hạn API key** theo IP/domain tại Google Cloud Console
4. **Rate limiting** cho endpoints
5. **Validate input** để tránh injection

### Cấu hình Google API Key

1. Truy cập Google Cloud Console
2. Chọn project
3. API & Services → Credentials
4. Chọn API key → Edit
5. Thêm restrictions:
   - Application restrictions: HTTP referrers
   - API restrictions: Generative Language API

## Troubleshooting

### Lỗi kết nối database
```
pymysql.err.OperationalError: (2003, "Can't connect to MySQL server")
```
**Giải pháp:** Kiểm tra DB_HOST, DB_USER, DB_PASS trong `.env`

### Lỗi Gemini API
```
google.api_core.exceptions.PermissionDenied: 403 API key not valid
```
**Giải pháp:** Kiểm tra GOOGLE_API_KEY trong `.env`

### CORS Error từ frontend
```
Access to fetch at 'http://127.0.0.1:5000/api/chat' has been blocked by CORS policy
```
**Giải pháp:** Server đã cài `flask-cors`, đảm bảo import và sử dụng `CORS(app)`

## Performance Tips

1. **Cache vouchers** - Vouchers ít thay đổi, có thể cache 5-10 phút
2. **Index database** - Đã có index cho session_id, user_id, created_at
3. **Limit response** - Giới hạn số recommendations (4-5 items)
4. **Async processing** - Xử lý training data async nếu lượng lớn

## Roadmap

- [ ] Thêm sentiment analysis
- [ ] Multi-language support
- [ ] Voice input/output
- [ ] Image recognition (upload ảnh để tìm sản phẩm tương tự)
- [ ] Fine-tune model với training data
- [ ] A/B testing different prompts
- [ ] Analytics dashboard

## License

MIT License - GoodZStore 2024
