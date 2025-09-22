<?php
// Hàm tính tổng số lượng sản phẩm trong giỏ hàng
function getCartItemCount() {
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

// Hàm tính tổng tiền giỏ hàng
function getCartTotal($conn) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    
    $total = 0;
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    $sql = "SELECT id, price FROM products WHERE id IN ($ids_string)";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($product = $result->fetch_assoc()) {
            $total += $product['price'] * $_SESSION['cart'][$product['id']];
        }
    }
    
    return $total;
}
?>