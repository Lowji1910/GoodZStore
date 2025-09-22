
<?php
// User checkout page
include_once __DIR__ . '/../header.php';
?>
<main>
    <h2>Thông tin thanh toán</h2>
    <form class="checkout-form">
        <input type="text" placeholder="Tên người mua" required>
        <input type="text" placeholder="Địa chỉ nhận hàng" required>
        <input type="tel" placeholder="Số điện thoại" required>
        <select required>
            <option value="cod">Thanh toán khi nhận hàng</option>
            <option value="bank">Chuyển khoản ngân hàng</option>
        </select>
        <textarea placeholder="Ghi chú đơn hàng" rows="2"></textarea>
        <button type="submit" class="btn btn-order">Đặt hàng</button>
    </form>
    <div class="checkout-summary">
        <h3>Đơn hàng của bạn</h3>
        <ul class="checkout-list">
            <li>Áo Thun Nam Cao Cấp x1 <span>299,000đ</span></li>
            <li>Quần Jeans Nữ Thời Trang x2 <span>798,000đ</span></li>
        </ul>
        <p>Tổng tiền: <span>1.097.000đ</span></p>
        <p>Giảm giá: <span>100.000đ</span></p>
        <p>Thành tiền: <span>997.000đ</span></p>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/checkout.css">
<script src="../ui.js"></script>