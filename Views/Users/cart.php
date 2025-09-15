<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="../index.php">Quay lại Trang chủ</a>
    </header>
    <main>
        <h2>Giỏ hàng của bạn</h2>
        <table class="cart-table">
            <thead>
                <tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Xóa</th></tr>
            </thead>
            <tbody>
                <!-- Dữ liệu sản phẩm trong giỏ -->
            </tbody>
        </table>
        <div class="cart-summary">
            <p>Tổng tiền: <span id="total">0đ</span></p>
            <p>Giảm giá: <span id="discount">0đ</span></p>
            <p>Thành tiền: <span id="final">0đ</span></p>
            <a href="../checkout.php" class="btn">Thanh toán</a>
        </div>
    </main>
</body>
</html>