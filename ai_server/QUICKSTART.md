# 🚀 HƯỚNG DẪN CHẠY NHANH - AI Server

## Bước 1: Cài đặt Dependencies

```bash
cd ai_server
pip install flask pymysql python-dotenv google-generativeai flask-cors
```

Hoặc:

```bash
pip install -r requirements.txt
```

## Bước 2: Tạo Database Tables

1. Mở **phpMyAdmin** hoặc **MySQL Workbench**
2. Chọn database `goodzstore`
3. Import file: `migrations/create_ai_tables.sql`

Hoặc dùng command line:

```bash
mysql -u root -p goodzstore < ../migrations/create_ai_tables.sql
```

## Bước 3: Kiểm tra file .env

File `ai_server/.env` đã có sẵn:

```env
GOOGLE_API_KEY=AIzaSyCNOMzJsgx1CsBbjOpkfRMo4Lf8_RUCgrM
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=goodzstore
PORT=5000
```

**Lưu ý:** Nếu MySQL của bạn có password, sửa dòng `DB_PASS=`

## Bước 4: Chạy Flask Server

```bash
cd ai_server
python app.py
```

Bạn sẽ thấy:

```
 * Running on http://127.0.0.1:5000
 * Debug mode: on
```

## Bước 5: Test API

### Cách 1: Dùng test script

```bash
python test_api.py
```

### Cách 2: Dùng browser

Mở trình duyệt và truy cập:

```
http://localhost/GoodZStore/Views/Users/product.php?id=1
```

Cuộn xuống phần **"Trợ lý AI - Tư vấn thời trang"** và thử chat!

### Cách 3: Dùng Postman/cURL

```bash
curl -X POST http://127.0.0.1:5000/api/chat ^
  -H "Content-Type: application/json" ^
  -d "{\"message\":\"Tôi cao 170cm, nên mặc size nào?\",\"user_id\":null,\"session_id\":\"test-001\",\"metadata\":{\"product_id\":1,\"height_cm\":170}}"
```

## Bước 6: Kiểm tra Admin Panel

Truy cập:

```
http://localhost/GoodZStore/Views/Admins/admin_ai_training.php
```

(Cần đăng nhập admin trước)

## Kiểm tra nhanh

✅ **Flask server đang chạy?**
- Mở http://127.0.0.1:5000 → Nếu không lỗi = OK

✅ **Database tables đã tạo?**
```sql
SHOW TABLES LIKE 'ai_%';
```
Phải thấy: `ai_conversations`, `ai_training_data`

✅ **Frontend có chatbox?**
- Vào trang product → Phải thấy box "Trợ lý AI"

## Troubleshooting

### Lỗi: ModuleNotFoundError

```bash
pip install <tên-module-bị-thiếu>
```

### Lỗi: Can't connect to MySQL

Sửa file `.env`:
- Kiểm tra `DB_HOST` (thường là `127.0.0.1` hoặc `localhost`)
- Kiểm tra `DB_USER` và `DB_PASS`
- Đảm bảo MySQL đang chạy

### Lỗi: CORS

Server đã cấu hình CORS. Nếu vẫn lỗi:
- Kiểm tra `flask-cors` đã cài chưa
- Restart Flask server

### Lỗi: Gemini API

- Kiểm tra `GOOGLE_API_KEY` trong `.env`
- API key phải còn quota
- Kiểm tra tại: https://makersuite.google.com/

## Demo Queries

Thử các câu hỏi sau trong chatbox:

1. **Size:** "Tôi cao 170cm, nên mặc size nào?"
2. **Promo:** "Có mã giảm giá không?"
3. **Style:** "Áo này phối với quần gì đẹp?"
4. **General:** "Sản phẩm này có màu nào?"

## Cấu trúc Files

```
GoodZStore/
├── ai_server/
│   ├── app.py              ← Flask server chính
│   ├── .env                ← Cấu hình (API key, DB)
│   ├── requirements.txt    ← Dependencies
│   ├── test_api.py         ← Test script
│   ├── README.md           ← Tài liệu chi tiết
│   ├── DEPLOYMENT.md       ← Hướng dẫn deploy
│   └── QUICKSTART.md       ← File này
├── migrations/
│   └── create_ai_tables.sql ← SQL tạo tables
├── Views/
│   ├── Users/
│   │   └── product.php     ← Trang có chatbox
│   └── Admins/
│       └── admin_ai_training.php ← Quản lý training data
```

## Next Steps

1. ✅ Chạy Flask server
2. ✅ Test chatbox trên product page
3. ✅ Chat vài câu để tạo data
4. ✅ Vào admin panel xem conversations
5. ✅ Chọn conversations tốt → Add to training data
6. ✅ Gán label phù hợp

## Support

Nếu gặp vấn đề:
1. Kiểm tra logs trong terminal Flask
2. Kiểm tra Console browser (F12)
3. Đọc file README.md để biết chi tiết
