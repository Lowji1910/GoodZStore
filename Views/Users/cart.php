
<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/cart_functions.php';

// Khởi tạo giỏ hàng trong session nếu chưa có
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Xử lý hành động giỏ hàng
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $qty = max(1, intval($_POST['quantity'] ?? 1));
        if ($product_id > 0) {
            $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $qty;
        }
        header('Location: cart.php'); exit;
    }
    if ($action === 'update') {
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $pid => $q) {
                $pid = intval($pid); $q = intval($q);
                if ($pid > 0) {
                    if ($q <= 0) unset($_SESSION['cart'][$pid]);
                    else $_SESSION['cart'][$pid] = $q;
                }
            }
        }
        header('Location: cart.php'); exit;
    }
    if ($action === 'remove') {
        $product_id = intval($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$product_id]);
        header('Location: cart.php'); exit;
    }
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header('Location: cart.php'); exit;
    }
}

// Lấy dữ liệu sản phẩm trong giỏ
$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids_sql = implode(',', $ids);
    $sql = "SELECT p.id, p.name, p.price, i.image_url
            FROM products p
            LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
            WHERE p.id IN ($ids_sql)";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['id']];
            $row['qty'] = $qty;
            $row['subtotal'] = $qty * $row['price'];
            $items[] = $row;
            $total += $row['subtotal'];
        }
    }
}
?>
<main>
    <h2>Giỏ hàng của bạn</h2>
    <?php if (empty($items)): ?>
        <p>Giỏ hàng trống. <a href="products.php">Tiếp tục mua sắm</a></p>
    <?php else: ?>
    <form method="post">
        <input type="hidden" name="action" value="update">
        <table class="cart-table">
            <thead>
                <tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Tạm tính</th><th>Xóa</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td>
                        <div class="cart-product">
                            <img src="/GoodZStore/uploads/<?= htmlspecialchars($it['image_url'] ?? 'no-image.jpg') ?>" alt="<?= htmlspecialchars($it['name']) ?>" class="cart-img">
                            <span><?= htmlspecialchars($it['name']) ?></span>
                        </div>
                    </td>
                    <td style="min-width:120px;">
                        <input type="number" name="qty[<?= $it['id'] ?>]" value="<?= $it['qty'] ?>" min="0" class="cart-qty">
                    </td>
                    <td><?= number_format($it['price'], 0, ',', '.') ?>đ</td>
                    <td><?= number_format($it['subtotal'], 0, ',', '.') ?>đ</td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $it['id'] ?>">
                            <button class="btn btn-remove" type="submit">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cart-actions" style="margin:12px 0; display:flex; gap:8px;">
            <button type="submit" class="btn">Cập nhật giỏ hàng</button>
            <form method="post" onsubmit="return confirm('Xóa toàn bộ giỏ hàng?');">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-danger">Xóa giỏ hàng</button>
            </form>
        </div>
    </form>
    <div class="cart-summary">
        <p>Tổng tiền: <span id="total"><?= number_format($total, 0, ',', '.') ?>đ</span></p>
        <a href="checkout.php" class="btn btn-checkout">Thanh toán</a>
    </div>
    <?php endif; ?>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/cart.css">
<script src="../ui.js"></script>