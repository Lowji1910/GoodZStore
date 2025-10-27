import os, json, time
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
        
        # Get product recommendations if product_id exists
        recommendations = []
        if product_id:
            recommendations = recommend_products(conn, product_id, limit=4)
        
        # Prepare prompt for Gemini
        system_instruction = """
        Bạn là trợ lý tư vấn thời trang bằng tiếng Việt. Trả lời ngắn gọn (2-4 câu). 
        Nếu user hỏi về size, đưa size gợi ý dựa trên measurements nếu có. 
        Nếu user đang xem một sản phẩm, đưa tối đa 3 gợi ý sản phẩm từ cùng category. 
        Liệt kê voucher active nếu phù hợp. Không chế tạo mã voucher. 
        Trả lời thân thiện, dễ hiểu.
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
        
        # Build response
        result = {
            "text": bot_text,
            "session_id": session_id,
            "size_suggestion": {"size": size_suggestion, "reason": size_reason} if size_suggestion else None,
            "recommendations": recommendations,
            "vouchers": ctx.get('vouchers', [])
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
