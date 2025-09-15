<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="../index.php">Quay lại Trang chủ</a>
    </header>
    <main>
        <h2>Thông tin thanh toán</h2>
        <form class="checkout-form">
            <input type="text" placeholder="Tên người mua" required>
            <input type="text" placeholder="Địa chỉ" required>
            <input type="tel" placeholder="Số điện thoại" required>
            <select>
                <option>Thanh toán khi nhận hàng</option>
                <option>Chuyển khoản ngân hàng</option>
            </select>
            <button type="submit">Đặt hàng</button>
        </form>
    </main>
</body>
</html>