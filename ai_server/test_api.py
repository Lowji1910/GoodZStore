"""
Test script for Flask AI Server API endpoints
Run: python test_api.py
"""

import requests
import json
import time

BASE_URL = "http://127.0.0.1:5000"

def print_section(title):
    print("\n" + "="*60)
    print(f"  {title}")
    print("="*60)

def test_chat_endpoint():
    print_section("TEST 1: Chat Endpoint - Size Question")
    
    payload = {
        "message": "Tôi cao 170cm, nặng 65kg, nên mặc size nào?",
        "user_id": None,
        "session_id": f"test-{int(time.time())}",
        "metadata": {
            "product_id": 1,
            "height_cm": 170,
            "weight_kg": 65
        }
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/chat", json=payload)
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"\n✅ Response:")
            print(f"  AI: {data.get('text', 'N/A')}")
            
            if data.get('size_suggestion'):
                print(f"\n📏 Size Suggestion:")
                print(f"  Size: {data['size_suggestion']['size']}")
                print(f"  Reason: {data['size_suggestion']['reason']}")
            
            if data.get('recommendations'):
                print(f"\n🛍️ Recommendations: {len(data['recommendations'])} products")
                for rec in data['recommendations'][:3]:
                    print(f"  - {rec['name']}: {rec['price']:,}đ")
            
            if data.get('vouchers'):
                print(f"\n🎟️ Vouchers: {len(data['vouchers'])} available")
                for v in data['vouchers']:
                    print(f"  - {v['code']}")
        else:
            print(f"❌ Error: {response.text}")
    
    except Exception as e:
        print(f"❌ Exception: {e}")

def test_chat_promo():
    print_section("TEST 2: Chat Endpoint - Promo Question")
    
    payload = {
        "message": "Có mã giảm giá nào không?",
        "user_id": None,
        "session_id": f"test-{int(time.time())}",
        "metadata": {
            "product_id": 2
        }
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/chat", json=payload)
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"\n✅ Response:")
            print(f"  AI: {data.get('text', 'N/A')}")
            
            if data.get('vouchers'):
                print(f"\n🎟️ Available Vouchers:")
                for v in data['vouchers']:
                    discount = f"{v['discount_value']}%" if v['discount_type'] == 'percentage' else f"{v['discount_value']:,}đ"
                    print(f"  - Code: {v['code']}")
                    print(f"    Discount: {discount}")
                    if v.get('min_order_amount', 0) > 0:
                        print(f"    Min Order: {v['min_order_amount']:,}đ")
        else:
            print(f"❌ Error: {response.text}")
    
    except Exception as e:
        print(f"❌ Exception: {e}")

def test_chat_style_advice():
    print_section("TEST 3: Chat Endpoint - Style Advice")
    
    payload = {
        "message": "Áo này phối với quần gì đẹp?",
        "user_id": None,
        "session_id": f"test-{int(time.time())}",
        "metadata": {
            "product_id": 3
        }
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/chat", json=payload)
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"\n✅ Response:")
            print(f"  AI: {data.get('text', 'N/A')}")
            
            if data.get('recommendations'):
                print(f"\n🛍️ Product Recommendations:")
                for rec in data['recommendations']:
                    print(f"  - {rec['name']}: {rec['price']:,}đ")
                    print(f"    Link: /product.php?id={rec['id']}")
        else:
            print(f"❌ Error: {response.text}")
    
    except Exception as e:
        print(f"❌ Exception: {e}")

def test_size_endpoint():
    print_section("TEST 4: Size Endpoint")
    
    payload = {
        "product_id": 1,
        "user_id": None,
        "session_id": f"test-{int(time.time())}",
        "measurements": {
            "height_cm": 165,
            "weight_kg": 60
        }
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/size", json=payload)
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"\n✅ Response:")
            print(f"  Suggested Size: {data.get('size', 'N/A')}")
            print(f"  Reason: {data.get('reason', 'N/A')}")
            
            if data.get('available_sizes'):
                print(f"\n📦 Available Sizes: {', '.join(data['available_sizes'])}")
        else:
            print(f"❌ Error: {response.text}")
    
    except Exception as e:
        print(f"❌ Exception: {e}")

def test_error_handling():
    print_section("TEST 5: Error Handling - Missing Required Fields")
    
    # Test missing message
    payload = {
        "user_id": None,
        "session_id": "test-error"
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/chat", json=payload)
        print(f"Status Code: {response.status_code}")
        print(f"Response: {response.json()}")
    except Exception as e:
        print(f"Exception: {e}")

def test_conversation_flow():
    print_section("TEST 6: Conversation Flow (Multiple Messages)")
    
    session_id = f"test-flow-{int(time.time())}"
    
    messages = [
        "Xin chào, tôi muốn mua áo",
        "Tôi cao 175cm",
        "Size nào phù hợp?",
        "Có khuyến mãi không?"
    ]
    
    for i, msg in enumerate(messages, 1):
        print(f"\n--- Message {i} ---")
        payload = {
            "message": msg,
            "user_id": 123,
            "session_id": session_id,
            "metadata": {
                "product_id": 1,
                "height_cm": 175 if i >= 2 else None
            }
        }
        
        try:
            response = requests.post(f"{BASE_URL}/api/chat", json=payload)
            if response.status_code == 200:
                data = response.json()
                print(f"User: {msg}")
                print(f"AI: {data.get('text', 'N/A')[:100]}...")
            else:
                print(f"Error: {response.status_code}")
        except Exception as e:
            print(f"Exception: {e}")
        
        time.sleep(0.5)  # Small delay between messages

def check_server():
    print_section("Checking Server Status")
    
    try:
        response = requests.get(f"{BASE_URL}/")
        print(f"✅ Server is running")
        return True
    except requests.exceptions.ConnectionError:
        print(f"❌ Server is not running at {BASE_URL}")
        print(f"\nPlease start the server first:")
        print(f"  cd ai_server")
        print(f"  python app.py")
        return False

def main():
    print("\n" + "🤖 Flask AI Server - API Testing".center(60, "="))
    print(f"Target: {BASE_URL}\n")
    
    if not check_server():
        return
    
    # Run all tests
    test_chat_endpoint()
    time.sleep(1)
    
    test_chat_promo()
    time.sleep(1)
    
    test_chat_style_advice()
    time.sleep(1)
    
    test_size_endpoint()
    time.sleep(1)
    
    test_error_handling()
    time.sleep(1)
    
    test_conversation_flow()
    
    print_section("Testing Complete")
    print("\n✅ All tests finished!")
    print("\nNext steps:")
    print("  1. Check database for saved conversations")
    print("  2. Visit admin panel: Views/Admins/admin_ai_training.php")
    print("  3. Test frontend integration on product pages")

if __name__ == "__main__":
    main()
