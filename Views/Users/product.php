<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <a href="../index.php">Quay lại Trang chủ</a>
    </header>
    <main>
        <div class="product-detail">
            <div class="product-image">
                <img src="img/sample.jpg" alt="Sản phẩm" id="mainImg" onclick="zoomImage(this)">
            </div>
            <div class="product-info">
                <h2>Tên sản phẩm</h2>
                <p class="price">1.000.000đ</p>
                <p class="desc">Mô tả sản phẩm chi tiết...</p>
                <form>
                    <label>Kích cỡ:
                        <select><option>S</option><option>M</option><option>L</option></select>
                    </label>
                    <label>Màu sắc:
                        <select><option>Đen</option><option>Trắng</option></select>
                    </label>
                    <label>Số lượng:
                        <input type="number" value="1" min="1">
                    </label>
                    <button type="submit">Thêm vào giỏ</button>
                </form>
            </div>
        </div>
        <section class="reviews">
            <h3>Đánh giá & Bình luận</h3>
            <div class="review-list">
                <!-- Danh sách đánh giá -->
            </div>
            <form class="review-form">
                <input type="text" placeholder="Viết bình luận...">
                <button>Gửi</button>
            </form>
        </section>
    </main>
</body>
</html>