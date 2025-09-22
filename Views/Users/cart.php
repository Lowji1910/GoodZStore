
<?php
// User cart page
include_once __DIR__ . '/../header.php';
?>
<main>
    <h2>Giỏ hàng của bạn</h2>
    <table class="cart-table">
        <thead>
            <tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Xóa</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="cart-product">
                        <img src="../img/product1.jpg" alt="Áo Thun Nam" class="cart-img">
                        <span>Áo Thun Nam Cao Cấp</span>
                    </div>
                </td>
                <td><input type="number" value="1" min="1" class="cart-qty"></td>
                <td>299,000đ</td>
                <td><button class="btn btn-remove">Xóa</button></td>
            </tr>
            <tr>
                <td>
                    <div class="cart-product">
                        <img src="../img/product2.jpg" alt="Quần Jeans Nữ" class="cart-img">
                        <span>Quần Jeans Nữ Thời Trang</span>
                    </div>
                </td>
                <td><input type="number" value="2" min="1" class="cart-qty"></td>
                <td>399,000đ</td>
                <td><button class="btn btn-remove">Xóa</button></td>
            </tr>
        </tbody>
    </table>
    <div class="cart-summary">
        <p>Tổng tiền: <span id="total">1.097.000đ</span></p>
        <p>Giảm giá: <span id="discount">100.000đ</span></p>
        <p>Thành tiền: <span id="final">997.000đ</span></p>
        <a href="checkout.php" class="btn btn-checkout">Thanh toán</a>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/cart.css">
<script src="../ui.js"></script>