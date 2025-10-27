"""
Script để kiểm tra và test các model Gemini có sẵn
Chạy: python fix_gemini.py
"""

import os
from dotenv import load_dotenv
import google.generativeai as genai

load_dotenv()

# Configure API
api_key = os.getenv("GOOGLE_API_KEY")
if not api_key:
    print("❌ GOOGLE_API_KEY not found in .env file")
    exit(1)

print(f"✅ API Key found: {api_key[:20]}...")
genai.configure(api_key=api_key)

print("\n" + "="*60)
print("  Danh sách Models có sẵn")
print("="*60)

try:
    # List all available models
    models = genai.list_models()
    
    print("\nCác model hỗ trợ generateContent:")
    supported_models = []
    
    for model in models:
        if 'generateContent' in model.supported_generation_methods:
            print(f"  ✅ {model.name}")
            supported_models.append(model.name)
    
    if not supported_models:
        print("  ❌ Không tìm thấy model nào hỗ trợ generateContent")
        print("\n⚠️ Có thể API key chưa được kích hoạt hoặc hết quota")
        print("   Vui lòng kiểm tra tại: https://makersuite.google.com/")
        exit(1)
    
    # Test with the first available model
    print("\n" + "="*60)
    print("  Test Model")
    print("="*60)
    
    test_model_name = supported_models[0].replace('models/', '')
    print(f"\nĐang test với model: {test_model_name}")
    
    model = genai.GenerativeModel(test_model_name)
    response = model.generate_content("Xin chào, bạn là ai?")
    
    print(f"\n✅ Test thành công!")
    print(f"Response: {response.text[:100]}...")
    
    # Update app.py suggestion
    print("\n" + "="*60)
    print("  Khuyến nghị")
    print("="*60)
    print(f"\nSử dụng model này trong app.py:")
    print(f"  model = genai.GenerativeModel('{test_model_name}')")
    
    # Write to a config file
    with open('model_config.txt', 'w', encoding='utf-8') as f:
        f.write(f"RECOMMENDED_MODEL={test_model_name}\n")
        f.write(f"\nAvailable models:\n")
        for m in supported_models:
            f.write(f"  - {m}\n")
    
    print(f"\n✅ Đã lưu cấu hình vào: model_config.txt")

except Exception as e:
    print(f"\n❌ Lỗi: {e}")
    print("\nCác bước kiểm tra:")
    print("  1. API key có đúng không?")
    print("  2. API key đã được kích hoạt chưa?")
    print("  3. Có quota còn lại không?")
    print("  4. Kết nối internet có ổn định không?")
    print("\nKiểm tra tại: https://makersuite.google.com/app/apikey")
