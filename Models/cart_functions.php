<?php
// Hàm tính tổng số lượng sản phẩm trong giỏ hàng
// Cart format: [['product_id' => 1, 'size_id' => 2, 'quantity' => 3], ...]
function getCartItemCount() {
    // Check if user is logged in
    if (isset($_SESSION['user']['id']) || isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'];
        // Need database connection here. 
        // Since this function might be called where $conn isn't available in scope, 
        // we might need to require db.php if not already, or pass $conn.
        // However, usually $conn is global or available. 
        // To be safe, let's assume $conn is available via global or require it.
        global $conn;
        if (!$conn) {
            require __DIR__ . '/db.php';
        }
        
        $sql = "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return (int)$row['total'];
        }
        return 0;
    }

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    
    $count = 0;
    foreach ($_SESSION['cart'] as $key => $item) {
        if (is_int($item)) {
            $count += $item;
        } 
        elseif (is_array($item) && isset($item['quantity'])) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

// Hàm tính tổng tiền giỏ hàng
function getCartTotal($conn) {
    if (isset($_SESSION['user']['id']) || isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'];
        $sql = "SELECT SUM(ci.quantity * p.price) as total 
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return (float)$row['total'];
        }
        return 0;
    }

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    
    $total = 0;
    foreach ($_SESSION['cart'] as $key => $item) {
        $product_id = 0;
        $quantity = 0;
        
        if (is_int($item)) {
            $product_id = $key;
            $quantity = $item;
        }
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