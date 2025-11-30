import os, json, time
import re
from pathlib import Path
from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
from dotenv import load_dotenv
import google.generativeai as genai

ROOT_ENV_PATH = Path(__file__).resolve().parents[1] / ".env"
load_dotenv(ROOT_ENV_PATH)
genai.configure(api_key=os.getenv("GOOGLE_API_KEY"))

DB_CONFIG = dict(
    host=os.getenv("DB_HOST","127.0.0.1"),
    user=os.getenv("DB_USER","root"),
    password=os.getenv("DB_PASS",""),
    db=os.getenv("DB_NAME","goodzstore"),
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor
)

# Base URL để tạo đường dẫn sản phẩm trong câu trả lời AI (có thể cấu hình trong .env)
SITE_BASE_URL = os.getenv("SITE_URL", "http://127.0.0.1/GoodZStore/Views/Users/")

def get_conn():
    return pymysql.connect(**DB_CONFIG)

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

def save_conv(conn, user_id, session_id, direction, message, intent=None, metadata=None):
    with conn.cursor() as cur:
        cur.execute("""
            INSERT INTO ai_conversations (user_id, session_id, direction, intent, message, metadata)
            VALUES (%s,%s,%s,%s,%s,%s)
        """, (user_id, session_id, direction, intent, message, json.dumps(metadata) if metadata else None))
    conn.commit()

def add_training_entry(conn, source, ref_id, text, label=None):
    with conn.cursor() as cur:
        cur.execute("""
            INSERT INTO ai_training_data (source, ref_id, text, label)
            VALUES (%s,%s,%s,%s)
        """, (source, ref_id, text, label))
    conn.commit()

def safe_user_id(conn, user_id):
    """Return user_id only if it exists in users table; otherwise None to satisfy FK."""
    try:
        if not user_id:
            return None
        with conn.cursor() as cur:
            cur.execute("SELECT id FROM users WHERE id=%s", (user_id,))
            row = cur.fetchone()
            return row['id'] if row else None
    except Exception:
        return None

# Helper: lấy product + sizes + vouchers active
def get_context(conn, product_id=None):
    ctx = {}
    with conn.cursor() as cur:
        if product_id:
            cur.execute("SELECT id,name,slug,description,price,category_id FROM products WHERE id=%s", (product_id,))
            ctx['product'] = cur.fetchone()
            if ctx['product']:
                cur.execute("SELECT size_name,stock_quantity FROM product_sizes WHERE product_id=%s", (product_id,))
                ctx['sizes'] = cur.fetchall()
        # vouchers active
        cur.execute("SELECT code,discount_type,discount_value,min_order_amount FROM vouchers WHERE NOW() BETWEEN start_date AND end_date")
        ctx['vouchers'] = cur.fetchall()
    return ctx

def _to_number(val):
    try:
        if val is None:
            return 0
        if isinstance(val, (int, float)):
            return val
        # Handle Decimal or str
        return float(val)
    except Exception:
        return 0

def normalize_vouchers(vouchers):
    """Ensure numeric fields are numbers to avoid client-side type errors."""
    out = []
    for v in vouchers or []:
        out.append({
            'code': str(v.get('code', '')),
            'discount_type': str(v.get('discount_type', '')),
            'discount_value': _to_number(v.get('discount_value')),
            'min_order_amount': _to_number(v.get('min_order_amount'))
        })
    return out

# Extract user's budget in VND from free text (e.g., "200k", "199.000", "200,000", "2 triệu")
def parse_budget_vnd(text: str) -> int:
    if not text:
        return 0
    t = text.lower()
    # Avoid matching units that are measurements (kg, cm, m followed by digit like 1m7)
    # Look for explicit money markers or common money units. Use word boundary and ensure unit is not followed by a digit (to avoid 1m7).
    # Patterns to match:
    #  - 199k, 200k, 200kđ, 200k đ
    #  - 200.000, 200,000
    #  - 2 triệu, 2tr, 2trieu
    money_patterns = [
        r"(\d+[\.,]?\d*)\s*(k|kđ|k đ|ngan|ngàn|nghin|nghìn)\b",
        r"(\d+[\.,]?\d*)\s*(triệu|trieu|tr|mio)\b",
        r"(\d{1,3}(?:[\.,]\d{3})+)\s*(đ|d|vnd)?\b",
        r"(\d+)\s*(đ|d|vnd)\b",
    ]

    for pat in money_patterns:
        m = re.search(pat, t)
        if m:
            num_str = m.group(1).replace('.', '').replace(',', '')
            try:
                val = float(num_str)
            except ValueError:
                return 0
            unit = (m.group(2) or '').strip() if len(m.groups()) >= 2 else ''
            if unit in ['k', 'kđ', 'k đ', 'ngan', 'ngàn', 'nghin', 'nghìn']:
                return int(val * 1000)
            if unit in ['triệu', 'trieu', 'tr', 'mio']:
                return int(val * 1_000_000)
            # if explicit VND sign or number with separators, interpret as full VND
            return int(val)

    # As a fallback, try to find plain numbers that look like currency (>=1000)
    m2 = re.search(r"(\d{4,}[\d\.,]*)", t)
    if m2:
        num_str = m2.group(1).replace('.', '').replace(',', '')
        try:
            return int(float(num_str))
        except Exception:
            return 0

    return 0

def parse_measurements(text: str) -> dict:
    """Extract simple measurements from text: weight in kg and height in cm (or meters like 1m7).
    Returns dict with possible keys: 'weight_kg', 'height_cm', 'size'."""
    out = {}
    if not text:
        return out
    t = text.lower()
    # weight: 50kg, 50 kg
    m = re.search(r"(\d{2,3})\s*(kg|kilog|kilo)?\b", t)
    if m:
        try:
            out['weight_kg'] = int(m.group(1))
        except Exception:
            pass
    # height: 170cm, 170 cm, 1m7, 1.7m
    m2 = re.search(r"(\d{2,3})\s*(cm)\b", t)
    if m2:
        try:
            out['height_cm'] = int(m2.group(1))
        except Exception:
            pass
    else:
        m3 = re.search(r"(\d(?:[\.,]?\d)?)\s*m\b", t)
        if m3:
            try:
                val = float(m3.group(1).replace(',', '.'))
                out['height_cm'] = int(val * 100)
            except Exception:
                pass
        else:
            # patterns like 1m7 (common in Vietnamese)
            m4 = re.search(r"1m(\d{1})\b", t)
            if m4:
                try:
                    cm = 100 + int(m4.group(1)) * 10
                    out['height_cm'] = cm
                except Exception:
                    pass
    # size like 'size M' or just 'M'
    m5 = re.search(r"size\s*([xsmlXL]{1,3})\b", t)
    if m5:
        out['size'] = m5.group(1).upper()

    return out

def build_deterministic_text(recommendations, budget, size_suggestion=None, size_reason=None, vouchers=None, include_links=True):
    parts = []
    if size_suggestion:
        parts.append(f"Gợi ý size: {size_suggestion}{' (' + size_reason + ')' if size_reason else ''}.")
    if recommendations:
        # Include product links if allowed. Format: Name (link) or just Name
        items = []
        for r in recommendations[:3]:
            name = r.get('name', '')
            url = r.get('url') or (r.get('slug') and f"{SITE_BASE_URL}product.php?id={r.get('id')}")
            if include_links and url:
                items.append(f"[{name}]({url})")
            else:
                items.append(name)
        names = ", ".join([i for i in items if i])
        if names:
            lead = "Gợi ý phù hợp" + (" theo ngân sách" if budget else "")
            parts.append(f"{lead}: {names}.")
    elif budget and budget > 0:
        parts.append(f"Trong tầm khoảng {budget:,}đ, bạn có thể ưu tiên chất liệu cotton, form basic, màu dễ phối. Nếu cần, mình sẽ lọc thêm sản phẩm đúng ngân sách.")
    if vouchers:
        codes = ", ".join([v.get('code','') for v in vouchers if v.get('code')])
        if codes:
            parts.append(f"Mã giảm giá hiện có: {codes}.")
    out = " ".join(parts).strip()
    if not out:
        # Safe default without naming products — be polite and invite next input
        out = "Mình có thể lọc sản phẩm theo nhu cầu hoặc ngân sách của bạn. Bạn muốn mình tìm theo từ khóa hay theo mức giá cụ thể nào?"
    # Add a polite closing to make replies friendlier
    if out:
        out = out.strip()
        if not out.endswith('?') and not out.endswith('!'):
            out = out + " Bạn cần mình giúp gì thêm?"
    return out

def is_greeting(text: str) -> bool:
    if not text:
        return False
    t = text.strip().lower()
    # If message is short and matches common greetings
    if len(t) <= 12 and re.match(r'^(hi|hello|chao|chào|xin chào|hey|helo)\b', t):
        return True
    return False

# Very simple keyword extraction -> patterns to search in product names
def detect_keywords(text: str):
    if not text:
        return []
    t = text.lower()
    candidates = [
        'áo', 'áo thun', 'thun', 'sơ mi', 'so mi', 'áo sơ mi', 'jean', 'jeans', 'quần', 'quần jean', 'quần short',
        'kaki', 'giày', 'sneaker', 'túi', 'pijama', 'đầm', 'váy', 'khoác', 'nam', 'nữ', 'nu'
    ]
    found = []
    for c in candidates:
        if c in t:
            found.append(c)
    # Deduplicate and prefer longer phrases
    found = sorted(set(found), key=lambda x: (-len(x), x))
    return found[:3]

def detect_gender(text: str):
    """Detect gender intent from text. Returns 'Male', 'Female', 'Unisex' or None."""
    t = text.lower()
    if re.search(r'\b(nam|trai|man|boy)\b', t):
        return 'Male'
    if re.search(r'\b(nữ|nu|gái|woman|girl|váy|đầm)\b', t):
        return 'Female'
    return None

def detect_intent(text: str) -> str:
    """Rough intent detection: returns one of 'greeting', 'ask_size', 'ask_recommend', 'ask_voucher', 'ask_budget', 'other'"""
    if not text:
        return 'other'
    t = text.lower()
    # greeting
    if is_greeting(t):
        return 'greeting'
    # size questions
    if re.search(r"\b(size|mặc size|mặc cỡ|nên mặc|bao nhiêu kg|kg cao|cao|cân nặng|mấy size|mặc size gì)\b", t) or re.search(r"\b\d+\s*kg\b", t) or re.search(r"\b\d+\s*cm\b", t) or 'kg' in t or 'cm' in t:
        return 'ask_size'
    # ask for recommendations explicitly
    if re.search(r"\b(gợi ý|gợi ý 3|gợi ý 2|gợi ý mấy|gợi ý cho tôi|gợi ý sản phẩm|gợi ý 3 sản phẩm|gợi ý 3 món)\b", t):
        return 'ask_recommend'
    # ask about vouchers/promotions
    if re.search(r"\b(voucher|mã giảm giá|ưu đãi|khuyến mãi|ưu dai|ưu đãi)\b", t):
        return 'ask_voucher'
    # budget-related
    if parse_budget_vnd(t) > 0:
        return 'ask_budget'
    return 'other'



CATEGORY_KEYWORDS = {
    'cong so': ['công sở', 'đi làm', 'công sở', 'văn phòng', 'đi làm'],
    'thoitrang_nam': ['nam', 'nữ', 'unisex'],
}

def map_category_from_text(text: str):
    t = text.lower()
    for cat, kws in CATEGORY_KEYWORDS.items():
        for k in kws:
            if k in t:
                return cat
    return None

def general_size_advice(measurements: dict):
    """Return (size, reason) as plain advice when product sizes not available.
    Uses simple height->size mapping as fallback."""
    h = measurements.get('height_cm')
    w = measurements.get('weight_kg')
    bmi = None
    try:
        if h and w:
            bmi = float(w) / ((float(h) / 100.0) ** 2)
    except Exception:
        bmi = None

    if h:
        if h < 165:
            s = 'S'
        elif h < 175:
            s = 'M'
        elif h < 185:
            s = 'L'
        else:
            s = 'XL'
        reason = f'Chiều cao {h}cm phù hợp size {s}'
        if bmi is not None:
            reason += f' (BMI khoảng {bmi:.1f})'
        return s, reason

    if w:
        # very rough weight-based fallback
        if w < 55:
            return 'S', f'Cân nặng {w}kg thường phù hợp size S-M'
        elif w < 70:
            return 'M', f'Cân nặng {w}kg thường phù hợp size M-L'
        else:
            return 'L', f'Cân nặng {w}kg thường phù hợp size L-XL'
    return None, 'Không có đủ thông tin để gợi ý size'

def suggest_size_rule(sizes, measurements: dict):
    """Suggest size based on measurements"""
    if not sizes:
        return None, "Không có thông tin size cho sản phẩm"
        
    # User-provided size
    if measurements.get('size'):
        s = str(measurements['size']).upper()
        for r in sizes:
            if str(r['size_name']).upper() == s and r.get('stock_quantity', 0) > 0:
                return r['size_name'], "Kích thước bạn chọn còn hàng"
    
    # Height-based suggestion with BMI adjustment when weight provided
    h = measurements.get('height_cm')
    w = measurements.get('weight_kg')
    bmi = None
    try:
        if h and w:
            bmi = float(w) / ((float(h) / 100.0) ** 2)
    except Exception:
        bmi = None

    if h:
        # prefer mappings: <165 S, 165-174 M, 175-184 L, >=185 XL
        if h < 165:
            pref = ['S', 'XS', '36', '37']
        elif h < 175:
            pref = ['M', 'S', '38', '39']
        elif h < 185:
            pref = ['L', 'M', '40', '41']
        else:
            pref = ['XL', 'XXL', '42', '43']

        # Adjust preference based on BMI: underweight -> prefer one size smaller; overweight -> one size larger
        if bmi is not None:
            if bmi < 18.5:
                # move preferences towards smaller sizes by appending smaller alternatives first
                pref = [p for p in pref if p not in ['XL','XXL']]  # minor heuristic
            elif bmi >= 25:
                # overweight: prefer larger sizes
                pref = ['L', 'XL', 'XXL'] + pref

        for p in pref:
            for r in sizes:
                if str(r['size_name']).upper().startswith(str(p)) and r.get('stock_quantity', 0) > 0:
                    reason = f"Gợi ý dựa trên chiều cao {h}cm"
                    if bmi is not None:
                        reason += f" và BMI khoảng {bmi:.1f}"
                    return r['size_name'], reason
    
    # Fallback: Most in-stock size
    if sizes:
        best = max(sizes, key=lambda x: x.get('stock_quantity', 0))
        return best['size_name'], "Kích thước có sẵn nhiều nhất"
    
    return None, "Không thể đề xuất size"

def recommend_products(conn, product_id, limit=4):
    """Recommend similar products in the same category"""
    try:
        with conn.cursor() as cur:
            # Get product category and price
            cur.execute("""
                SELECT category_id, price 
                FROM products 
                WHERE id = %s
            """, (product_id,))
            product = cur.fetchone()
            
            if not product:
                return []
            
            # Find similar products
            cur.execute("""
                SELECT p.id, p.name, p.slug, p.price, pi.image_url 
                FROM products p
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
                WHERE p.category_id = %s 
                AND p.id != %s 
                AND p.stock_quantity > 0
                ORDER BY ABS(p.price - %s) 
                LIMIT %s
            """, (product['category_id'], product_id, product['price'], limit))
            
            rows = cur.fetchall()
            # Attach product URL for frontend/backend to include links
            for r in rows:
                try:
                    r['url'] = f"{SITE_BASE_URL}product.php?id={r.get('id')}"
                except Exception:
                    r['url'] = None
            return rows
            
    except Exception as e:
        print(f"Error in recommend_products: {e}")
        return []

def get_chat_history(conn, session_id, limit=6):
    """Lấy lịch sử chat gần nhất để AI hiểu ngữ cảnh"""
    history = []
    if not session_id:
        return history
        
    try:
        with conn.cursor() as cur:
            # Lấy các tin nhắn gần nhất (trừ tin nhắn hiện tại đang xử lý)
            cur.execute("""
                SELECT direction, message 
                FROM ai_conversations 
                WHERE session_id = %s 
                ORDER BY id DESC 
                LIMIT %s
            """, (session_id, limit))
            
            rows = cur.fetchall()
            # Đảo ngược lại để đúng thứ tự thời gian (Cũ -> Mới)
            for row in reversed(rows):
                role = "User" if row['direction'] == 'user' else "Bot"
                history.append(f"{role}: {row['message']}")
    except Exception as e:
        print(f"Error fetching history: {e}")
        
    return "\n".join(history)

@app.route("/api/chat", methods=["POST"])
def chat():
    """Main chat endpoint"""
    payload = request.json or {}
    user_id = payload.get('user_id')
    session_id = payload.get('session_id') or f"ses-{int(time.time())}"
    message = payload.get('message', '').strip()
    metadata = payload.get('metadata', {})
    
    if not message:
        return jsonify({"error": "Message is required"}), 400
    
    conn = get_conn()
    try:
        # Validate user_id to avoid FK constraint failures
        valid_user_id = safe_user_id(conn, user_id)
        # Save user message
        save_conv(conn, valid_user_id, session_id, 'user', message, metadata=metadata)
        
        # Get context based on product_id if available
        product_id = metadata.get('product_id')
        ctx = get_context(conn, product_id)
        # Normalize vouchers to ensure numeric types
        ctx['vouchers'] = normalize_vouchers(ctx.get('vouchers', []))
        
        # Generate size suggestion if product has sizes
        size_suggestion = None
        size_reason = None
        if ctx.get('sizes'):
            # Merge measurements from metadata (if provided) with any measurements parsed from the message text
            parsed = parse_measurements(message)
            measurements = {}
            if isinstance(metadata.get('measurements'), dict):
                measurements.update(metadata.get('measurements'))
            measurements.update(parsed)
            size_suggestion, size_reason = suggest_size_rule(ctx['sizes'], measurements)
        
        # Get product recommendations
        recommendations = []
        if product_id:
            # Similar items to current product
            recommendations = recommend_products(conn, product_id, limit=4)
        else:
            # Try budget-based recommendations from message, respecting keywords if present
            budget = parse_budget_vnd(message)
            gender_filter = detect_gender(message)
            
            if budget and budget > 0:
                try:
                    print(f"[AI] Budget parsed: {budget}, Gender: {gender_filter}")
                    keys = detect_keywords(message)
                    
                    sql = """
                        SELECT p.id, p.name, p.slug, p.price, pi.image_url
                        FROM products p
                        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
                        WHERE p.price <= %s AND p.stock_quantity > 0
                    """
                    params = [budget]
                    
                    if gender_filter:
                        sql += " AND (p.gender = %s OR p.gender = 'Unisex')"
                        params.append(gender_filter)

                    if keys:
                        print(f"[AI] Budget + Keywords: {keys}")
                        # Use AND for stricter filtering (e.g. "áo" AND "nam")
                        like_clauses = " AND ".join(["p.name LIKE %s"] * len(keys))
                        sql += f" AND ({like_clauses})"
                        params.extend([f"%{k}%" for k in keys])
                    
                    sql += " ORDER BY p.is_featured DESC, p.price ASC LIMIT 3"
                    
                    with conn.cursor() as cur:
                        cur.execute(sql, params)
                        recommendations = cur.fetchall()
                    print(f"[AI] Budget recommendations count: {len(recommendations) if recommendations else 0}")
                except Exception as e:
                    print(f"Budget recommendation error: {e}")

            # If no budget recs (or no budget), try keyword-based search
            if not recommendations and not budget:
                keys = detect_keywords(message)
                gender_filter = detect_gender(message)
                if keys:
                    try:
                        print(f"[AI] Keyword search: {keys}, Gender: {gender_filter}")
                        # Use AND for stricter filtering
                        like_clauses = " AND ".join(["p.name LIKE %s"] * len(keys))
                        params = [f"%{k}%" for k in keys]
                        
                        sql = f"""
                                SELECT p.id, p.name, p.slug, p.price, pi.image_url
                                FROM products p
                                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
                                WHERE ({like_clauses}) AND p.stock_quantity > 0
                                """
                        if gender_filter:
                            sql += " AND (p.gender = %s OR p.gender = 'Unisex')"
                            params.append(gender_filter)
                            
                        sql += " ORDER BY p.is_featured DESC, p.price ASC LIMIT 3"

                        with conn.cursor() as cur:
                            cur.execute(sql, params)
                            recommendations = cur.fetchall()
                        print(f"[AI] Keyword recommendations count: {len(recommendations) if recommendations else 0}")
                    except Exception as e:
                        print(f"Keyword recommendation error: {e}")

            # Ensure each recommendation has a URL field for frontend/AI text
            try:
                for r in (recommendations or []):
                    if not r.get('url'):
                        r['url'] = f"{SITE_BASE_URL}product.php?id={r.get('id')}"
            except Exception:
                pass
        
        # Prepare prompt for Gemini
        system_instruction = """
        Bạn là **Trợ lý Tư vấn Thời Trang GoodZ**, nói tiếng Việt thân thiện và ngắn gọn (2–4 câu mỗi lần trả lời).

🎯 **Mục tiêu:**  
Giúp người dùng chọn sản phẩm thời trang phù hợp (về size, chất liệu, phong cách, ngân sách) dựa trên dữ liệu thực tế trong cơ sở dữ liệu `goodzstore`.

---

### 🧩 **Cấu trúc dữ liệu bạn có thể truy cập**
CSDL `goodzstore` gồm các bảng chính:
- `products`: chứa thông tin sản phẩm (id, name, description, price, category_id, size, color, material, gender, image, stock)
- `categories`: phân loại sản phẩm (áo, quần, váy, giày, phụ kiện, v.v.)
- `vouchers`: chứa thông tin khuyến mãi (code, discount_percent, min_order, start_date, end_date, status)
- `users`: thông tin người dùng (để gợi ý size theo giới tính, chiều cao, cân nặng)
- `orders` và `order_details`: dữ liệu lịch sử mua hàng (để hiểu sở thích người dùng)
- `reviews`: đánh giá sản phẩm, giúp AI hiểu sản phẩm nào phổ biến.

---

### 🪄 **Nguyên tắc trả lời**
1. **KHÔNG** bao giờ tự xưng là "AI:", "Bot:", "Trợ lý:" ở đầu câu trả lời. Hãy trả lời trực tiếp.
2. Luôn nói **ngắn gọn, tự nhiên, thân thiện** (2–4 câu).  
3. Không hiển thị dữ liệu SQL thô, chỉ diễn giải thân thiện.  
4. Khi backend gửi danh sách `recommendations`, hãy trình bày tên sản phẩm dưới dạng link Markdown: `[Tên sản phẩm](URL)`.
5. Nếu `recommendations` rỗng → không nêu sản phẩm cụ thể, chỉ tư vấn về chất liệu, kiểu dáng, cách phối hoặc ngân sách.  
6. Khi người dùng hỏi về **size**, dùng dữ liệu trong cột `size` của bảng `products`, hoặc dựa theo `users.height`, `users.weight` nếu có.  
7. Khi có `vouchers` đang hoạt động (`status = 'active'` và `start_date <= NOW() <= end_date`), liệt kê **đúng mã và mô tả ưu đãi**; không tự bịa.  
8. Nếu người dùng đã từng mua sản phẩm (`orders`, `order_details`), có thể gợi ý dựa trên **phong cách hoặc danh mục tương tự** (`category_id` giống nhau).

---

### 🧵 **Cách phản hồi từng tình huống**

#### 🧍‍♂️ Khi người dùng hỏi về size:
- Nếu có `height` và `weight` từ user:
  > Với chiều cao {{height}}cm và cân nặng {{weight}}kg, bạn nên chọn size {{calculated_size}} cho vừa người nhé.  
  > Nếu muốn mặc thoải mái hơn, có thể thử size lớn hơn một bậc.
- Nếu không có dữ liệu cá nhân:
  > Size M thường vừa cho người cao khoảng 1m65–1m70. Nếu bạn cao hơn thì chọn L nhé.

#### 👕 Khi có danh sách `recommendations`:
> Mình thấy bạn có thể thích *{{product_1}}*, *{{product_2}}* và *{{product_3}}*.  
> Cả 3 mẫu này đều dễ phối đồ và đang được đánh giá cao.  
> Hiện có voucher “{{voucher_code}}” giảm {{discount_percent}}% cho đơn từ {{min_order}}đ.

#### 💬 Khi không có recommendations:
> Với dáng người nhỏ gọn, bạn nên chọn vải cotton hoặc linen để thoáng mát.  
> Ưu tiên tông màu sáng và form suông để trông cao hơn.  
> Hiện chưa có sản phẩm cụ thể phù hợp trong kho nhé.

#### 🎁 Khi có voucher:
> Hiện shop có mã “{{voucher_code}}” giảm {{discount_percent}}% cho đơn hàng từ {{min_order}}đ, áp dụng đến {{end_date}} nhé.

---

### ❌ **Không được làm**
- **KHÔNG** bắt đầu câu bằng "AI:", "Bot:", "GoodZ AI:".
- Không bịa tên sản phẩm, voucher, hoặc giá.
- Không hiển thị truy vấn SQL hoặc dữ liệu thô.
- Không bình luận chủ quan về người dùng.
- Không trả lời vượt ngoài chủ đề thời trang hoặc dữ liệu trong DB.

---

### ✅ **Ví dụ trả lời mẫu**
**Người dùng:** Mình cao 1m68, nặng 60kg, nên chọn size nào cho áo thun cotton?

> Với chiều cao 168cm và cân nặng 60kg, bạn nên chọn size M cho vừa người nhé.  
> Nếu thích form rộng thì có thể thử size L.  
> Mẫu áo thun cotton này dễ phối với quần jeans hoặc short.

---

**Người dùng:** Có mẫu nào hợp đi làm không?

> Mình gợi ý bạn thử *Áo sơ mi linen cổ tàu*, *Quần tây slimfit*, và *Áo polo cotton trơn*.  
> Cả 3 đều hợp môi trường công sở, dễ phối giày da hoặc sneakers.  
> Hiện đang có voucher “OFF10” giảm 10% cho đơn từ 499k.

---

**Người dùng:** Có ưu đãi gì không?

> Hiện shop có mã “SALE10” giảm 10% cho đơn từ 499k và “FREESHIP” miễn phí giao hàng toàn quốc.  
> Bạn có thể áp dụng khi thanh toán nhé.

---

### 🧭 **Mục tiêu cuối cùng**
- Trả lời như một stylist thân thiện, hiểu dữ liệu thực của GoodZStore.  
- Dựa vào bảng SQL thật để tư vấn chính xác (size, voucher, danh mục, xu hướng).  
- Không bao giờ nói thông tin không có trong database hoặc không được backend cung cấp.


        """
        
        # [THÊM MỚI] Lấy lịch sử chat
        history_text = get_chat_history(conn, session_id, limit=6)

        # Build context text
        context_parts = []
        if ctx.get('product'):
            p = ctx['product']
            context_parts.append(f"Người dùng ĐANG XEM sản phẩm: {p['name']} (Giá: {p['price']:,}đ). \nLƯU Ý QUAN TRỌNG: Mọi câu hỏi của người dùng (ví dụ: 'nó có tốt không', 'chất liệu gì', 'tư vấn size') đều mặc định là hỏi về sản phẩm này, trừ khi người dùng nói rõ tên sản phẩm khác.")
            
            # Add size info if available
            if size_suggestion:
                context_parts.append(f"Gợi ý size: {size_suggestion} ({size_reason})")
        
        if ctx.get('vouchers'):
            vouchers = ", ".join([str(v.get('code', '')) for v in ctx['vouchers']])
            context_parts.append(f"Mã giảm giá hiện có: {vouchers}")
        
        # Add recommendations names (whitelist) for the model to reference
        if recommendations:
            rec_list = "\n".join([f"- {r.get('name')}: {r.get('url')}" for r in recommendations])
            context_parts.append(f"Danh sách sản phẩm gợi ý (hãy dùng link này): \n{rec_list}")
        context_text = "\n".join(context_parts)
        
        # Combine into final prompt
        # Sửa lại prompt để bao gồm lịch sử
        prompt = f"""{system_instruction}
        
        Lịch sử hội thoại (để hiểu ngữ cảnh):
        ---
        {history_text}
        ---

        Ngữ cảnh dữ liệu hiện tại (Sản phẩm/Voucher):
        {context_text}
        
        Câu hỏi mới nhất của User: {message}
        """
        
        # Determine budget early for deterministic logic
        budget_for_debug = parse_budget_vnd(message) if not metadata.get('product_id') else 0

        # Only mention vouchers and product links in the assistant's first reply for a given session or user.
        include_vouchers = True
        try:
            with conn.cursor() as cur:
                if session_id:
                    cur.execute("SELECT COUNT(*) AS cnt FROM ai_conversations WHERE session_id=%s AND direction='bot'", (session_id,))
                    row = cur.fetchone()
                    if row and row.get('cnt', 0) > 0:
                        include_vouchers = False
                elif valid_user_id:
                    cur.execute("SELECT COUNT(*) AS cnt FROM ai_conversations WHERE user_id=%s AND direction='bot'", (valid_user_id,))
                    row = cur.fetchone()
                    if row and row.get('cnt', 0) > 0:
                        include_vouchers = False
        except Exception as e:
            print(f"Error checking prior bot messages: {e}")
            # If DB check fails, default to including vouchers (safer fallback)
            include_vouchers = True

        vouchers_for_output = ctx.get('vouchers', []) if include_vouchers else []

        # If message is a simple greeting, do not treat it as substantive: do not include vouchers or links
        greet = is_greeting(message)
        if greet:
            include_vouchers = False
            vouchers_for_output = []

        # Fetch prior bot recommendations (if any) to include in context (without links)
        prior_recommendations = []
        try:
            with conn.cursor() as cur:
                if session_id:
                    cur.execute("SELECT metadata FROM ai_conversations WHERE session_id=%s AND direction='bot' ORDER BY id DESC LIMIT 1", (session_id,))
                    row = cur.fetchone()
                    if row and row.get('metadata'):
                        try:
                            meta = json.loads(row.get('metadata'))
                            prior_recommendations = meta.get('recommendations', []) if isinstance(meta, dict) else []
                        except Exception:
                            prior_recommendations = []
                elif valid_user_id:
                    cur.execute("SELECT metadata FROM ai_conversations WHERE user_id=%s AND direction='bot' ORDER BY id DESC LIMIT 1", (valid_user_id,))
                    row = cur.fetchone()
                    if row and row.get('metadata'):
                        try:
                            meta = json.loads(row.get('metadata'))
                            prior_recommendations = meta.get('recommendations', []) if isinstance(meta, dict) else []
                        except Exception:
                            prior_recommendations = []
        except Exception as e:
            print(f"Error fetching prior recommendations: {e}")

        # Detect intent early to choose fast-paths
        intent = detect_intent(message)

        # Fast-path: voucher question
        if intent == 'ask_voucher':
            # If we are allowed to include vouchers in this reply
            if vouchers_for_output and len(vouchers_for_output) > 0:
                parts = []
                for v in vouchers_for_output:
                    if v.get('discount_type') == 'percentage':
                        disc = f"{int(v.get('discount_value',0))}%"
                    else:
                        try:
                            disc = f"{int(v.get('discount_value',0)):,}đ"
                        except Exception:
                            disc = str(v.get('discount_value',''))
                    min_order = v.get('min_order_amount', 0)
                    min_text = f" (Đơn tối thiểu {int(min_order):,}đ)" if min_order and int(min_order) > 0 else ""
                    parts.append(f"{v.get('code')} — Giảm {disc}{min_text}")
                bot_text = "Hiện shop có các mã giảm giá sau: " + "; ".join(parts) + ". Bạn muốn mình hướng dẫn cách áp dụng không?"
            else:
                bot_text = "Hiện tại shop không có mã giảm giá đang hoạt động hoặc mình không thể hiển thị mã ngay bây giờ. Bạn muốn mình kiểm tra theo điều kiện (ví dụ: đơn tối thiểu hoặc loại sản phẩm) không?"

            result = {
                "text": bot_text,
                "session_id": session_id,
                "size_suggestion": None,
                "recommendations": [],
                "vouchers": vouchers_for_output,
                "prev_recommendations": prior_recommendations,
                "debug": {"intent": intent}
            }

            bot_metadata = {"recommendations": [], "vouchers_included": bool(vouchers_for_output)}
            save_conv(conn, valid_user_id, session_id, 'bot', bot_text, None, bot_metadata)
            add_training_entry(conn, 'conversation', None, json.dumps({"user": message, "bot": bot_text, "metadata": bot_metadata}), label=None)
            return jsonify(result)

        # Fast-path: size question
        if intent == 'ask_size':
            # Use parsed measurements and metadata to suggest size
            parsed = parse_measurements(message)
            measurements = {}
            if isinstance(metadata.get('measurements'), dict):
                measurements.update(metadata.get('measurements'))
            measurements.update(parsed)

            if ctx.get('sizes'):
                size_suggestion, size_reason = suggest_size_rule(ctx['sizes'], measurements)
                bot_text = f"Với thông tin của bạn ({measurements.get('height_cm','?')}cm, {measurements.get('weight_kg','?')}kg), gợi ý size: {size_suggestion}. {size_reason}. Bạn muốn mình so sánh thêm với các mẫu cụ thể không?"
            else:
                # No product-specific sizes: give general advice
                gs, gr = general_size_advice(measurements)
                if gs:
                    bot_text = f"Với thông tin {measurements.get('height_cm','?')}cm và {measurements.get('weight_kg','?')}kg, mình gợi ý size {gs}. {gr}. Bạn muốn mình lọc sản phẩm theo size này không?"
                else:
                    bot_text = "Mình cần chiều cao hoặc cân nặng để gợi ý size chính xác hơn — bạn cho mình biết chiều cao (cm) và cân nặng (kg) nhé?"

            # Prepare response and save
            result = {
                "text": bot_text,
                "session_id": session_id,
                "size_suggestion": {"size": size_suggestion, "reason": size_reason} if size_suggestion else ( {"size": gs, "reason": gr} if gs else None ),
                "recommendations": [],
                "vouchers": [],
                "prev_recommendations": prior_recommendations,
                "debug": {"intent": intent}
            }
            bot_metadata = {"recommendations": [], "vouchers_included": False}
            save_conv(conn, valid_user_id, session_id, 'bot', bot_text, None, bot_metadata)
            add_training_entry(conn, 'conversation', None, json.dumps({"user": message, "bot": bot_text, "metadata": bot_metadata}), label=None)
            return jsonify(result)

        # Fast-path: explicit recommendation request (e.g., 'gợi ý 3 sản phẩm công sở')
        if intent == 'ask_recommend':
            cat = map_category_from_text(message)
            recs = []
            try:
                with conn.cursor() as cur:
                    if cat == 'cong so':
                        # find products in categories containing 'công sở' or office-related; use categories table mapping if available
                        cur.execute("SELECT id FROM categories WHERE name LIKE %s LIMIT 1", ("%công%",))
                        crow = cur.fetchone()
                        if crow:
                            cur.execute(
                                "SELECT p.id, p.name, p.slug, p.price, pi.image_url FROM products p LEFT JOIN product_images pi ON pi.product_id=p.id AND pi.is_main=1 WHERE p.category_id=%s AND p.stock_quantity>0 ORDER BY p.is_featured DESC LIMIT 3",
                                (crow.get('id'),)
                            )
                            recs = cur.fetchall()
                    # fallback: try keyword detection
                    if not recs:
                        keys = detect_keywords(message)
                        if keys:
                            like_clauses = " OR ".join(["p.name LIKE %s"] * len(keys))
                            params = [f"%{k}%" for k in keys]
                            cur.execute(f"SELECT p.id,p.name,p.slug,p.price,pi.image_url FROM products p LEFT JOIN product_images pi ON pi.product_id=p.id AND pi.is_main=1 WHERE ({like_clauses}) AND p.stock_quantity>0 ORDER BY p.is_featured DESC LIMIT 3", params)
                            recs = cur.fetchall()
            except Exception as e:
                print(f"Recommend fast-path error: {e}")

            # Ensure urls
            try:
                for r in (recs or []):
                    if not r.get('url'):
                        r['url'] = f"{SITE_BASE_URL}product.php?id={r.get('id')}"
            except Exception:
                pass

            # Build polite reply
            if recs and len(recs) > 0:
                names = ", ".join([f"[{r.get('name')}]({r.get('url')})" for r in recs[:3]])
                bot_text = f"Mình gợi ý những mẫu phù hợp: {names}. Bạn muốn xem chi tiết mẫu nào?"
            else:
                bot_text = "Mình chưa tìm thấy sản phẩm phù hợp ngay bây giờ — bạn muốn mình lọc theo giá hoặc theo từ khóa cụ thể không?"

            result = {"text": bot_text, "session_id": session_id, "recommendations": recs, "vouchers": vouchers_for_output, "prev_recommendations": prior_recommendations, "debug": {"intent": intent}}
            bot_metadata = {"recommendations": [{"id": r.get('id'), "name": r.get('name'), "url": r.get('url')} for r in (recs or [])], "vouchers_included": bool(vouchers_for_output)}
            save_conv(conn, valid_user_id, session_id, 'bot', bot_text, None, bot_metadata)
            add_training_entry(conn, 'conversation', None, json.dumps({"user": message, "bot": bot_text, "metadata": bot_metadata}), label=None)
            return jsonify(result)

        # If this is the first bot reply for this session/user, include links; otherwise, don't include links but provide prior recs in context
        include_links = include_vouchers

        # If this is a greeting, prefer a friendly greeting reply and skip voucher/link insertion
        if greet:
            bot_text = "Chào bạn! Mình là trợ lý AI của GoodZStore — mình có thể giúp tìm sản phẩm, gợi ý size hoặc kiểm tra khuyến mãi. Bạn muốn mình giúp gì hôm nay?"
            # Build a polite deterministic fallback if needed later, but for greeting return early after saving metadata (no vouchers/links)
            deterministic = None

        if not greet:
            deterministic = build_deterministic_text(
                recommendations=recommendations or prior_recommendations,
                budget=budget_for_debug,
                size_suggestion=size_suggestion,
                size_reason=size_reason,
                vouchers=vouchers_for_output,
                include_links=include_links
            )

            if deterministic:
                bot_text = deterministic
            else:
                # If we couldn't build a deterministic reply, call Gemini as fallback
                try:
                    model_name = os.getenv("GEMINI_MODEL_NAME", "gemini-1.5-flash")
                    model = genai.GenerativeModel(model_name)
                    response = model.generate_content(prompt)
                    bot_text = response.text
                except Exception as gemini_error:
                    print(f"Gemini API Error: {gemini_error}")
                    bot_text = "Xin chào! Mình là trợ lý AI của GoodZStore. "
                    if size_suggestion:
                        bot_text += f"Dựa trên thông số của bạn, mình gợi ý size {size_suggestion}. "
                    if vouchers_for_output:
                        voucher_codes = ", ".join([v['code'] for v in vouchers_for_output])
                        bot_text += f"Hiện tại shop đang có các mã giảm giá: {voucher_codes}. "
                    bot_text += "Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!"

        # Clean up bot text to remove "AI:" prefix if model generates it
        if bot_text:
            bot_text = re.sub(r'^(\*\*|__)?\s*(AI|Assistant|Bot|GoodZ AI)\s*(\*\*|__)?\s*:\s*', '', bot_text, flags=re.IGNORECASE).strip()

        # Build response
        result = {
            "text": bot_text,
            "session_id": session_id,
            "size_suggestion": {"size": size_suggestion, "reason": size_reason} if size_suggestion else None,
            "recommendations": recommendations,
            "vouchers": vouchers_for_output,
            "prev_recommendations": prior_recommendations,
            "debug": {
                "product_id": product_id,
                "budget": budget_for_debug,
                "rec_count": len(recommendations) if recommendations else 0
            }
        }
        
        # Save bot response and include metadata (recommendations shown and whether vouchers were included)
        bot_metadata = {
            "recommendations": [
                {"id": r.get('id'), "name": r.get('name'), "url": r.get('url')} for r in (recommendations or [])
            ],
            "vouchers_included": include_vouchers
        }
        save_conv(conn, valid_user_id, session_id, 'bot', bot_text, None, bot_metadata)
        add_training_entry(
            conn, 
            'conversation', 
            None, 
            json.dumps({"user": message, "bot": bot_text, "metadata": bot_metadata}),
            label=None
        )
        
        return jsonify(result)
        
    except Exception as e:
        print(f"Error in chat endpoint: {e}")
        return jsonify({
            "error": "Đã có lỗi xảy ra. Vui lòng thử lại sau.",
            "details": str(e)
        }), 500
        
    finally:
        conn.close()

@app.route("/api/size", methods=["POST"])
def size_api():
    """Dedicated endpoint for size recommendations"""
    payload = request.json or {}
    product_id = payload.get('product_id')
    measurements = payload.get('measurements', {})
    
    if not product_id:
        return jsonify({"error": "product_id is required"}), 400
    
    conn = get_conn()
    try:
        with conn.cursor() as cur:
            cur.execute("""
                SELECT size_name, stock_quantity 
                FROM product_sizes 
                WHERE product_id = %s
            """, (product_id,))
            sizes = cur.fetchall()
        
        suggestion, reason = suggest_size_rule(sizes, measurements)
        
        # Log this interaction
        save_conv(
            conn, 
            payload.get('user_id'), 
            payload.get('session_id', f"ses-{int(time.time())}"), 
            'bot', 
            f"Gợi ý size: {suggestion} - {reason}", 
            intent='size_suggest', 
            metadata={"product_id": product_id, "measurements": measurements}
        )
        
        add_training_entry(
            conn, 
            'size_tool', 
            None, 
            f"product:{product_id} measurements:{json.dumps(measurements)} suggestion:{suggestion}", 
            label='size_suggest'
        )
        
        return jsonify({
            "size": suggestion, 
            "reason": reason,
            "available_sizes": [s['size_name'] for s in sizes if s.get('stock_quantity', 0) > 0]
        })
        
    except Exception as e:
        print(f"Error in size_api: {e}")
        return jsonify({"error": "Đã có lỗi xảy ra khi gợi ý size"}), 500
        
    finally:
        conn.close()

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=int(os.getenv("PORT", 5000)), debug=True)
