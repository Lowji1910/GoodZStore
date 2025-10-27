# 🔧 KHẮC PHỤC LỖI GEMINI API

## ❌ Lỗi hiện tại

```
404 models/gemini-1.5-flash is not found for API version v1beta
```

## 🔍 Nguyên nhân

1. **Phiên bản thư viện cũ** - `google-generativeai` version cũ không hỗ trợ model mới
2. **Tên model không đúng** - Tên model có thể thay đổi theo API version
3. **API key chưa kích hoạt** - API key chưa được enable cho Gemini API

## ✅ GIẢI PHÁP

### Giải pháp 1: Cập nhật thư viện (Khuyến nghị)

```bash
# Gỡ cài đặt version cũ
pip uninstall google-generativeai -y

# Cài đặt version mới nhất
pip install google-generativeai --upgrade

# Hoặc cài version cụ thể
pip install google-generativeai>=0.7.0
```

**Sau đó restart Flask server:**
```bash
# Ctrl+C để dừng server
# Chạy lại:
python app.py
```

### Giải pháp 2: Kiểm tra model có sẵn

Chạy script kiểm tra:

```bash
python fix_gemini.py
```

Script này sẽ:
- ✅ Liệt kê tất cả models có sẵn
- ✅ Test model đầu tiên
- ✅ Tạo file `model_config.txt` với model khuyến nghị

### Giải pháp 3: Sử dụng Fallback Response

Code đã được cập nhật với fallback mechanism. Nếu Gemini API lỗi, hệ thống sẽ:
- ✅ Vẫn trả về response (không crash)
- ✅ Hiển thị size suggestion
- ✅ Hiển thị vouchers
- ✅ Hiển thị recommendations

**Chatbot vẫn hoạt động**, chỉ không có AI text generation.

### Giải pháp 4: Tạo API key mới

Nếu API key hết quota hoặc không hoạt động:

1. Truy cập: https://makersuite.google.com/app/apikey
2. Click **"Create API Key"**
3. Chọn project (hoặc tạo mới)
4. Copy API key mới
5. Paste vào file `.env`:
   ```env
   GOOGLE_API_KEY=YOUR_NEW_KEY_HERE
   ```
6. Restart server

### Giải pháp 5: Sử dụng model cũ hơn

Nếu các giải pháp trên không work, thử model cũ:

Sửa trong `app.py` dòng 195:

```python
# Thử các model này theo thứ tự:
model = genai.GenerativeModel('gemini-pro')           # Thử đầu tiên
# model = genai.GenerativeModel('gemini-1.0-pro')     # Backup 1
# model = genai.GenerativeModel('text-bison-001')     # Backup 2
```

## 🧪 Test sau khi sửa

### Test 1: Kiểm tra thư viện

```bash
pip show google-generativeai
```

Phải thấy version >= 0.7.0

### Test 2: Kiểm tra models

```bash
python fix_gemini.py
```

Phải thấy danh sách models

### Test 3: Test API

```bash
python test_api.py
```

Phải thấy Status Code 200 cho các test chat

### Test 4: Test trên website

1. Vào: http://localhost/GoodZStore/Views/Users/product.php?id=1
2. Chat: "Tôi cao 170cm, nên mặc size nào?"
3. Phải thấy response (có thể là fallback hoặc AI)

## 📊 Kiểm tra logs

Khi chạy Flask server, xem terminal:

**Nếu thấy:**
```
Gemini API Error: 404 models/...
```
→ Gemini API lỗi, nhưng fallback response vẫn hoạt động ✅

**Nếu thấy:**
```
127.0.0.1 - - [27/Oct/2024 15:30:45] "POST /api/chat HTTP/1.1" 200 -
```
→ Request thành công ✅

**Nếu thấy:**
```
127.0.0.1 - - [27/Oct/2024 15:30:45] "POST /api/chat HTTP/1.1" 500 -
```
→ Server error, cần kiểm tra logs chi tiết

## 🎯 Kết quả mong đợi

Sau khi áp dụng giải pháp:

### Scenario 1: Gemini API hoạt động
```json
{
  "text": "Với chiều cao 170cm, mình gợi ý bạn nên chọn size M...",
  "size_suggestion": {"size": "M", "reason": "..."},
  "recommendations": [...],
  "vouchers": [...]
}
```

### Scenario 2: Gemini API lỗi (Fallback)
```json
{
  "text": "Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size M. Hiện tại shop đang có các mã giảm giá: SUMMER2024. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!",
  "size_suggestion": {"size": "M", "reason": "..."},
  "recommendations": [...],
  "vouchers": [...]
}
```

**Cả 2 scenario đều OK!** Chatbot vẫn hoạt động.

## 🔄 Quy trình debug đầy đủ

```bash
# Bước 1: Cập nhật thư viện
pip install google-generativeai --upgrade

# Bước 2: Kiểm tra models
python fix_gemini.py

# Bước 3: Restart server
# Ctrl+C trong terminal Flask
python app.py

# Bước 4: Test API
# Mở terminal mới
python test_api.py

# Bước 5: Test frontend
# Mở browser: http://localhost/GoodZStore/Views/Users/product.php?id=1
```

## 💡 Tips

1. **Luôn cập nhật thư viện mới nhất:**
   ```bash
   pip install --upgrade google-generativeai
   ```

2. **Kiểm tra quota API:**
   - Truy cập: https://makersuite.google.com/
   - Xem usage và limits

3. **Sử dụng fallback:**
   - Code đã có fallback, chatbot vẫn hoạt động dù Gemini lỗi

4. **Monitor logs:**
   - Xem terminal Flask để biết Gemini có hoạt động không

5. **Test từng bước:**
   - Test API riêng trước
   - Sau đó test frontend

## 📞 Nếu vẫn lỗi

### Kiểm tra:
1. ✅ Internet connection
2. ✅ API key đúng trong .env
3. ✅ Thư viện đã update
4. ✅ Flask server đã restart
5. ✅ MySQL đang chạy

### Logs cần xem:
- Terminal Flask server
- Browser Console (F12)
- File `model_config.txt` (sau khi chạy fix_gemini.py)

### Fallback luôn hoạt động:
Dù Gemini lỗi, chatbot vẫn:
- ✅ Gợi ý size
- ✅ Hiển thị vouchers
- ✅ Hiển thị recommendations
- ✅ Trả về response text (template)

## ✅ Checklist

- [ ] Đã chạy: `pip install google-generativeai --upgrade`
- [ ] Đã chạy: `python fix_gemini.py`
- [ ] Đã restart Flask server
- [ ] Đã test: `python test_api.py`
- [ ] Đã test trên website
- [ ] Chatbox hiển thị response (AI hoặc fallback)

---

**Lưu ý:** Với fallback mechanism, hệ thống sẽ **luôn hoạt động** dù Gemini API có vấn đề. Người dùng vẫn nhận được response hữu ích!
