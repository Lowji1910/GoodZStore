
<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/cart_functions.php';

// Khởi tạo giỏ hàng trong session nếu chưa có
// Format: $_SESSION['cart'] = [['product_id' => 1, 'size_id' => 2, 'quantity' => 3], ...]
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Convert old cart format to new format (migration)
if (!empty($_SESSION['cart'])) {
    $firstItem = reset($_SESSION['cart']);
    // Check if old format (array keys are product_ids, values are quantities)
    if (is_int($firstItem)) {
        $oldCart = $_SESSION['cart'];
        $_SESSION['cart'] = [];
        foreach ($oldCart as $pid => $qty) {
            $_SESSION['cart'][] = [
                'product_id' => $pid,
                'size_id' => 0,
                'quantity' => $qty
            ];
        }
    }
}

// Xử lý hành động giỏ hàng
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $size_id = intval($_POST['size_id'] ?? 0);
        $qty = max(1, intval($_POST['quantity'] ?? 1));
        
        if ($product_id > 0) {
            // Tìm xem đã có item này chưa (cùng product + size)
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id && $item['size_id'] == $size_id) {
                    $item['quantity'] += $qty;
                    $found = true;
                    break;
                }
            }
            
            // Nếu chưa có thì thêm mới
            if (!$found) {
                $_SESSION['cart'][] = [
                    'product_id' => $product_id,
                    'size_id' => $size_id,
                    'quantity' => $qty
                ];
            }
        }
        header('Location: cart.php'); exit;
    }
    if ($action === 'update') {
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $index => $q) {
                $index = intval($index);
                $q = intval($q);
                if (isset($_SESSION['cart'][$index])) {
                    if ($q <= 0) {
                        unset($_SESSION['cart'][$index]);
                    } else {
                        $_SESSION['cart'][$index]['quantity'] = $q;
                    }
                }
            }
            // Re-index array
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
        header('Location: cart.php'); exit;
    }
    if ($action === 'remove') {
        $index = intval($_POST['index'] ?? -1);
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
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
    foreach ($_SESSION['cart'] as $index => $cartItem) {
        $product_id = $cartItem['product_id'];
        $size_id = $cartItem['size_id'];
        $qty = $cartItem['quantity'];
        
        // Lấy thông tin sản phẩm
        $sql = "SELECT p.id, p.name, p.price, i.image_url
                FROM products p
                LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $row['cart_index'] = $index;
            $row['quantity'] = $qty;
            $row['size_id'] = $size_id;
            $row['size_name'] = null;
            
            // Lấy thông tin size nếu có
            if ($size_id > 0) {
                $sqlSize = "SELECT size_name FROM product_sizes WHERE id = ?";
                $stmtSize = $conn->prepare($sqlSize);
                $stmtSize->bind_param("i", $size_id);
                $stmtSize->execute();
                $resultSize = $stmtSize->get_result();
                if ($sizeRow = $resultSize->fetch_assoc()) {
                    $row['size_name'] = $sizeRow['size_name'];
                }
            }
            
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
                <tr><th>Sản phẩm</th><th>Size</th><th>Số lượng</th><th>Giá</th><th>Tạm tính</th><th>Xóa</th></tr>
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
                    <td>
                        <?php if ($it['size_name']): ?>
                            <span class="cart-size"><?= htmlspecialchars($it['size_name']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="min-width:120px;">
                        <input type="number" name="qty[<?= $it['cart_index'] ?>]" value="<?= $it['quantity'] ?>" min="0" class="cart-qty">
                    </td>
                    <td><?= number_format($it['price'], 0, ',', '.') ?>đ</td>
                    <td><?= number_format($it['subtotal'], 0, ',', '.') ?>đ</td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="index" value="<?= $it['cart_index'] ?>">
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