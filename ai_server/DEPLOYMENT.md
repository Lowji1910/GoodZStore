# Hướng dẫn Deployment - Flask AI Server

## Môi trường Development

### 1. Chuẩn bị

```bash
# Clone repository
git clone <your-repo-url>
cd GoodZStore/ai_server

# Tạo virtual environment (khuyến nghị)
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Cài đặt dependencies
pip install -r requirements.txt
```

### 2. Cấu hình Database

```bash
# Import database schema
mysql -u root -p goodzstore < ../migrations/create_ai_tables.sql

# Hoặc sử dụng phpMyAdmin
# Import file: migrations/create_ai_tables.sql
```

### 3. Cấu hình Environment

Tạo file `.env`:

```env
GOOGLE_API_KEY=AIzaSy...your_key_here
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=your_password
DB_NAME=goodzstore
PORT=5000
```

### 4. Chạy Development Server

```bash
python app.py
```

Server sẽ chạy tại: http://127.0.0.1:5000

## Testing

### 1. Test API với cURL

**Test Chat Endpoint:**

```bash
curl -X POST http://127.0.0.1:5000/api/chat \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Tôi cao 170cm, nên mặc size nào?",
    "user_id": null,
    "session_id": "test-session-001",
    "metadata": {
      "product_id": 1,
      "height_cm": 170
    }
  }'
```

**Test Size Endpoint:**

```bash
curl -X POST http://127.0.0.1:5000/api/size \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "user_id": null,
    "session_id": "test-session-002",
    "measurements": {
      "height_cm": 170,
      "weight_kg": 65
    }
  }'
```

### 2. Test với Postman

1. Import collection từ `postman_collection.json` (nếu có)
2. Hoặc tạo requests thủ công:
   - Method: POST
   - URL: http://127.0.0.1:5000/api/chat
   - Headers: Content-Type: application/json
   - Body: raw JSON (xem ví dụ trên)

### 3. Test Frontend Integration

1. Mở trình duyệt
2. Truy cập: http://localhost/GoodZStore/Views/Users/product.php?id=1
3. Cuộn xuống phần "Trợ lý AI"
4. Thử chat: "Tư vấn size cho tôi"

## Môi trường Production

### Option 1: Deploy trên VPS/Server riêng

#### 1. Cài đặt môi trường

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Cài Python 3.9+
sudo apt install python3 python3-pip python3-venv -y

# Cài MySQL
sudo apt install mysql-server -y

# Cài Nginx (reverse proxy)
sudo apt install nginx -y
```

#### 2. Setup Application

```bash
# Tạo user cho app
sudo useradd -m -s /bin/bash aiserver
sudo su - aiserver

# Clone code
git clone <your-repo-url>
cd GoodZStore/ai_server

# Virtual environment
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Copy .env
cp .env.example .env
nano .env  # Chỉnh sửa cấu hình
```

#### 3. Setup Gunicorn (WSGI Server)

```bash
# Cài Gunicorn
pip install gunicorn

# Test chạy
gunicorn -w 4 -b 0.0.0.0:5000 app:app

# Tạo systemd service
sudo nano /etc/systemd/system/aiserver.service
```

**Nội dung file aiserver.service:**

```ini
[Unit]
Description=GoodZStore AI Server
After=network.target

[Service]
User=aiserver
Group=aiserver
WorkingDirectory=/home/aiserver/GoodZStore/ai_server
Environment="PATH=/home/aiserver/GoodZStore/ai_server/venv/bin"
ExecStart=/home/aiserver/GoodZStore/ai_server/venv/bin/gunicorn -w 4 -b 127.0.0.1:5000 app:app

[Install]
WantedBy=multi-user.target
```

```bash
# Enable và start service
sudo systemctl enable aiserver
sudo systemctl start aiserver
sudo systemctl status aiserver
```

#### 4. Setup Nginx Reverse Proxy

```bash
sudo nano /etc/nginx/sites-available/aiserver
```

**Nội dung file:**

```nginx
server {
    listen 80;
    server_name ai.goodzstore.com;  # Thay bằng domain của bạn

    location /api/ {
        proxy_pass http://127.0.0.1:5000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # CORS headers
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Methods "GET, POST, OPTIONS";
        add_header Access-Control-Allow-Headers "Content-Type";
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/aiserver /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 5. Setup SSL (Let's Encrypt)

```bash
# Cài Certbot
sudo apt install certbot python3-certbot-nginx -y

# Lấy SSL certificate
sudo certbot --nginx -d ai.goodzstore.com

# Auto-renew
sudo systemctl enable certbot.timer
```

### Option 2: Deploy trên Cloud Platform

#### Google Cloud Run

```bash
# Tạo Dockerfile
cat > Dockerfile << EOF
FROM python:3.9-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

ENV PORT=8080
CMD exec gunicorn --bind :$PORT --workers 1 --threads 8 app:app
EOF

# Build và deploy
gcloud builds submit --tag gcr.io/PROJECT_ID/aiserver
gcloud run deploy aiserver --image gcr.io/PROJECT_ID/aiserver --platform managed
```

#### Heroku

```bash
# Tạo Procfile
echo "web: gunicorn app:app" > Procfile

# Deploy
heroku create goodzstore-ai
heroku config:set GOOGLE_API_KEY=your_key_here
git push heroku main
```

## Bảo mật Production

### 1. Environment Variables

**KHÔNG BAO GIỜ** commit file `.env` vào Git!

```bash
# Thêm vào .gitignore
echo ".env" >> .gitignore
echo "*.pyc" >> .gitignore
echo "__pycache__/" >> .gitignore
```

### 2. Firewall

```bash
# Chỉ cho phép port 80, 443, 22
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 3. Rate Limiting

Thêm vào Nginx config:

```nginx
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;

location /api/ {
    limit_req zone=api_limit burst=20 nodelay;
    # ... rest of config
}
```

### 4. API Key Restrictions

Tại Google Cloud Console:

1. API & Services → Credentials
2. Chọn API key → Edit
3. Application restrictions:
   - HTTP referrers: `https://goodzstore.com/*`
4. API restrictions:
   - Generative Language API only

### 5. Database Security

```sql
-- Tạo user riêng cho app
CREATE USER 'aiserver'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE ON goodzstore.ai_conversations TO 'aiserver'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON goodzstore.ai_training_data TO 'aiserver'@'localhost';
FLUSH PRIVILEGES;
```

## Monitoring & Logging

### 1. Application Logs

```python
# Thêm vào app.py
import logging

logging.basicConfig(
    filename='/var/log/aiserver/app.log',
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
```

### 2. System Monitoring

```bash
# Cài htop
sudo apt install htop

# Cài monitoring tools
sudo apt install prometheus-node-exporter
```

### 3. Error Tracking (Sentry)

```bash
pip install sentry-sdk[flask]
```

```python
# Thêm vào app.py
import sentry_sdk
from sentry_sdk.integrations.flask import FlaskIntegration

sentry_sdk.init(
    dsn="your-sentry-dsn",
    integrations=[FlaskIntegration()],
    traces_sample_rate=1.0
)
```

## Backup & Recovery

### 1. Database Backup

```bash
# Tạo cron job backup hàng ngày
crontab -e

# Thêm dòng:
0 2 * * * mysqldump -u root -p'password' goodzstore ai_conversations ai_training_data > /backup/ai_$(date +\%Y\%m\%d).sql
```

### 2. Application Backup

```bash
# Backup code và .env
tar -czf /backup/aiserver_$(date +%Y%m%d).tar.gz /home/aiserver/GoodZStore/ai_server
```

## Performance Optimization

### 1. Database Indexing

```sql
-- Đã có trong migration, kiểm tra:
SHOW INDEX FROM ai_conversations;
SHOW INDEX FROM ai_training_data;
```

### 2. Caching (Redis)

```bash
# Cài Redis
sudo apt install redis-server

# Cài Python Redis client
pip install redis
```

```python
# Thêm vào app.py
import redis
cache = redis.Redis(host='localhost', port=6379, db=0)

# Cache vouchers
def get_vouchers_cached():
    cached = cache.get('active_vouchers')
    if cached:
        return json.loads(cached)
    
    # Query from DB
    vouchers = get_vouchers_from_db()
    cache.setex('active_vouchers', 300, json.dumps(vouchers))  # Cache 5 phút
    return vouchers
```

### 3. Connection Pooling

```python
# Sử dụng connection pool cho MySQL
from pymysql.pooling import PooledConnection

db_pool = pymysql.connect(
    **DB_CONFIG,
    max_connections=20,
    stale_timeout=300
)
```

## Troubleshooting

### Lỗi thường gặp

**1. Port already in use**
```bash
# Tìm process đang dùng port 5000
lsof -i :5000
# Kill process
kill -9 <PID>
```

**2. Permission denied**
```bash
# Cấp quyền cho user
sudo chown -R aiserver:aiserver /home/aiserver/GoodZStore
```

**3. Database connection failed**
```bash
# Kiểm tra MySQL đang chạy
sudo systemctl status mysql

# Kiểm tra credentials
mysql -u root -p -e "SELECT 1"
```

**4. Gemini API quota exceeded**
- Kiểm tra quota tại Google Cloud Console
- Implement rate limiting
- Cache responses khi có thể

## Checklist Deployment

- [ ] Database tables đã tạo
- [ ] File .env đã cấu hình đúng
- [ ] Dependencies đã cài đặt
- [ ] Gunicorn đã setup
- [ ] Nginx reverse proxy đã cấu hình
- [ ] SSL certificate đã cài đặt
- [ ] Firewall đã cấu hình
- [ ] Logging đã setup
- [ ] Backup đã cấu hình
- [ ] Monitoring đã setup
- [ ] API key restrictions đã thiết lập
- [ ] Rate limiting đã enable
- [ ] Test endpoints thành công

## Support

Nếu gặp vấn đề, kiểm tra:
1. Logs: `/var/log/aiserver/app.log`
2. Nginx logs: `/var/log/nginx/error.log`
3. System logs: `journalctl -u aiserver -f`

---

**Lưu ý:** Thay thế các giá trị placeholder (domain, API keys, passwords) bằng giá trị thực tế của bạn.
