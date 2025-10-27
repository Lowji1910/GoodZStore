import os, json, time
import re
from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
from dotenv import load_dotenv
import google.generativeai as genai

load_dotenv()
genai.configure(api_key=os.getenv("GOOGLE_API_KEY"))

DB_CONFIG = dict(
    host=os.getenv("DB_HOST","127.0.0.1"),
    user=os.getenv("DB_USER","root"),
    password=os.getenv("DB_PASS",""),
    db=os.getenv("DB_NAME","goodzstore"),
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor
)

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
    # patterns like 199k, 200k
    m = re.search(r"(\d+[\.,]?\d*)\s*(k|ngan|ngàn|nghin|nghìn|triệu|tr|m|mio)?", t)
    if not m:
        return 0
    num_str = m.group(1).replace('.', '').replace(',', '')
    try:
        val = float(num_str)
    except ValueError:
        return 0
    unit = (m.group(2) or '').strip()
    if unit in ['k', 'ngan', 'ngàn', 'nghin', 'nghìn']:
        return int(val * 1000)
    if unit in ['triệu', 'tr', 'm', 'mio']:
        return int(val * 1_000_000)
    # if no unit but looks like a full VND amount
    return int(val)

def build_deterministic_text(recommendations, budget, size_suggestion=None, size_reason=None, vouchers=None):
    parts = []
    if size_suggestion:
        parts.append(f"Gợi ý size: {size_suggestion}{' (' + size_reason + ')' if size_reason else ''}.")
    if recommendations:
        names = ", ".join([r.get('name', '') for r in recommendations[:3] if r.get('name')])
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
        # Safe default without naming products
        out = "Mình có thể lọc sản phẩm theo nhu cầu hoặc ngân sách của bạn. Hãy cho mình biết từ khóa (ví dụ: áo thun, quần jean) hoặc mức giá mong muốn để mình gợi ý chính xác hơn."
    return out

# Very simple keyword extraction -> patterns to search in product names
def detect_keywords(text: str):
    if not text:
        return []
    t = text.lower()
    candidates = [
        'áo', 'áo thun', 'thun', 'sơ mi', 'so mi', 'áo sơ mi', 'jean', 'jeans', 'quần', 'quần jean', 'quần short',
        'kaki', 'giày', 'sneaker', 'túi', 'pijama', 'đầm', 'váy', 'khoác'
    ]
    found = []
    for c in candidates:
        if c in t:
            found.append(c)
    # Deduplicate and prefer longer phrases
    found = sorted(set(found), key=lambda x: (-len(x), x))
    return found[:3]

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
    
    # Height-based suggestion
    h = measurements.get('height_cm')
    if h:
        if h < 160:
            pref = ['S', 'XS', '36', '37']
        elif h < 170:
            pref = ['M', 'S', '38', '39']
        elif h < 180:
            pref = ['L', 'M', '40', '41']
        else:
            pref = ['XL', 'XXL', '42', '43']
            
        for p in pref:
            for r in sizes:
                if str(r['size_name']).upper().startswith(str(p)) and r.get('stock_quantity', 0) > 0:
                    return r['size_name'], f"Gợi ý dựa trên chiều cao {h}cm"
    
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
            
            return cur.fetchall()
            
    except Exception as e:
        print(f"Error in recommend_products: {e}")
        return []

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
            size_suggestion, size_reason = suggest_size_rule(ctx['sizes'], metadata)
        
        # Get product recommendations
        recommendations = []
        if product_id:
            # Similar items to current product
            recommendations = recommend_products(conn, product_id, limit=4)
        else:
            # Try budget-based recommendations from message
            budget = parse_budget_vnd(message)
            if budget and budget > 0:
                try:
                    print(f"[AI] Budget parsed: {budget}")
                    with conn.cursor() as cur:
                        cur.execute(
                            """
                            SELECT p.id, p.name, p.slug, p.price, pi.image_url
                            FROM products p
                            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
                            WHERE p.price <= %s AND p.stock_quantity > 0
                            ORDER BY p.is_featured DESC, p.price ASC
                            LIMIT 3
                            """,
                            (budget,)
                        )
                        recommendations = cur.fetchall()
                    print(f"[AI] Budget recommendations count: {len(recommendations) if recommendations else 0}")
                except Exception as e:
                    print(f"Budget recommendation error: {e}")
            # If no budget recs, try keyword-based search
            if not recommendations:
                keys = detect_keywords(message)
                if keys:
                    try:
                        print(f"[AI] Keyword search: {keys}")
                        like_clauses = " OR ".join(["p.name LIKE %s"] * len(keys))
                        params = [f"%{k}%" for k in keys]
                        with conn.cursor() as cur:
                            cur.execute(
                                f"""
                                SELECT p.id, p.name, p.slug, p.price, pi.image_url
                                FROM products p
                                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
                                WHERE ({like_clauses}) AND p.stock_quantity > 0
                                ORDER BY p.is_featured DESC, p.price ASC
                                LIMIT 3
                                """,
                                params
                            )
                            recommendations = cur.fetchall()
                        print(f"[AI] Keyword recommendations count: {len(recommendations) if recommendations else 0}")
                    except Exception as e:
                        print(f"Keyword recommendation error: {e}")
        
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
1. Luôn nói **ngắn gọn, tự nhiên, thân thiện** (2–4 câu).  
2. Không hiển thị dữ liệu SQL thô, chỉ diễn giải thân thiện.  
3. Khi backend gửi danh sách `recommendations` (lấy từ `products`), chỉ được nêu **tối đa 3 tên sản phẩm** trong danh sách này.  
4. Nếu `recommendations` rỗng → không nêu sản phẩm cụ thể, chỉ tư vấn về chất liệu, kiểu dáng, cách phối hoặc ngân sách.  
5. Khi người dùng hỏi về **size**, dùng dữ liệu trong cột `size` của bảng `products`, hoặc dựa theo `users.height`, `users.weight` nếu có.  
6. Khi có `vouchers` đang hoạt động (`status = 'active'` và `start_date <= NOW() <= end_date`), liệt kê **đúng mã và mô tả ưu đãi**; không tự bịa.  
7. Nếu người dùng đã từng mua sản phẩm (`orders`, `order_details`), có thể gợi ý dựa trên **phong cách hoặc danh mục tương tự** (`category_id` giống nhau).

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
        
        # Build context text
        context_parts = []
        if ctx.get('product'):
            p = ctx['product']
            context_parts.append(f"Sản phẩm hiện tại: {p['name']} - {p['price']:,}đ")
            
            # Add size info if available
            if size_suggestion:
                context_parts.append(f"Gợi ý size: {size_suggestion} ({size_reason})")
        
        if ctx.get('vouchers'):
            vouchers = ", ".join([str(v.get('code', '')) for v in ctx['vouchers']])
            context_parts.append(f"Mã giảm giá hiện có: {vouchers}")
        
        # Add recommendations names (whitelist) for the model to reference
        allowed_names = ", ".join([r.get('name','') for r in recommendations]) if recommendations else ""
        if allowed_names:
            context_parts.append(f"Chỉ được nhắc các sản phẩm: {allowed_names}")
        context_text = "\n".join(context_parts)
        
        # Combine into final prompt
        prompt = f"""{system_instruction}
        
        Ngữ cảnh:
        {context_text}
        
        Câu hỏi: {message}
        """
        
        # Call Gemini API
        try:
            model = genai.GenerativeModel('gemini-flash-latest')
            response = model.generate_content(prompt)
            bot_text = response.text
        except Exception as gemini_error:
            # Fallback response nếu Gemini lỗi
            print(f"Gemini API Error: {gemini_error}")
            bot_text = "Xin chào! Mình là trợ lý AI của GoodZStore. "
            
            if size_suggestion:
                bot_text += f"Dựa trên thông số của bạn, mình gợi ý size {size_suggestion}. "
            
            if ctx.get('vouchers'):
                voucher_codes = ", ".join([v['code'] for v in ctx['vouchers']])
                bot_text += f"Hiện tại shop đang có các mã giảm giá: {voucher_codes}. "
            
            bot_text += "Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!"
        
        # Enforce deterministic text to avoid invented names
        budget_for_debug = parse_budget_vnd(message) if not metadata.get('product_id') else 0
        deterministic = build_deterministic_text(
            recommendations=recommendations,
            budget=budget_for_debug,
            size_suggestion=size_suggestion,
            size_reason=size_reason,
            vouchers=ctx.get('vouchers', [])
        )
        if deterministic:
            bot_text = deterministic

        # Build response
        result = {
            "text": bot_text,
            "session_id": session_id,
            "size_suggestion": {"size": size_suggestion, "reason": size_reason} if size_suggestion else None,
            "recommendations": recommendations,
            "vouchers": ctx.get('vouchers', []),
            "debug": {
                "product_id": product_id,
                "budget": budget_for_debug,
                "rec_count": len(recommendations) if recommendations else 0
            }
        }
        
        # Save bot response
        save_conv(conn, valid_user_id, session_id, 'bot', bot_text)
        add_training_entry(
            conn, 
            'conversation', 
            None, 
            json.dumps({"user": message, "bot": bot_text, "metadata": metadata}),
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
