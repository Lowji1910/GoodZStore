<?php
// Hàm tính tổng số lượng sản phẩm trong giỏ hàng
// Cart format: [['product_id' => 1, 'size_id' => 2, 'quantity' => 3], ...]
function getCartItemCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    
    $count = 0;
    foreach ($_SESSION['cart'] as $key => $item) {
        // Old format: array keys are product_ids, values are quantities
        if (is_int($item)) {
            $count += $item;
        } 
        // New format: array of objects with 'quantity' key
        elseif (is_array($item) && isset($item['quantity'])) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

// Hàm tính tổng tiền giỏ hàng
function getCartTotal($conn) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    
    $total = 0;
    foreach ($_SESSION['cart'] as $key => $item) {
        $product_id = 0;
        $quantity = 0;
        
        // Old format: array keys are product_ids, values are quantities
        if (is_int($item)) {
            $product_id = $key;
            $quantity = $item;
        }
        // New format: array of objects
        elseif (is_array($item)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
        }
        
        if ($product_id > 0) {
            $sql = "SELECT price FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($product = $result->fetch_assoc()) {
                $total += $product['price'] * $quantity;
            }
        }
    }
    
    return $total;
}
?>