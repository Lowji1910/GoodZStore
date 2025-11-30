

<?php
ob_start();
session_start();
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/cart_functions.php';

// Include header
require_once __DIR__ . '/../../Views/header.php';

// Helper to get user ID
function get_current_user_id() {
    return $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
}

// Xử lý hành động giỏ hàng
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = get_current_user_id();
    
    // Enforce login for adding to cart
    if ($action === 'add') {
        if (!$user_id) {
            header('Location: /GoodZStore/Views/Users/auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }

        $product_id = intval($_POST['product_id'] ?? 0);
        $size_id = !empty($_POST['size_id']) ? intval($_POST['size_id']) : null;
        $qty = max(1, intval($_POST['quantity'] ?? 1));
        
        if ($product_id > 0) {
            // Check if item exists in DB
            if ($size_id) {
                $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND size_id = ?");
                $stmt->bind_param("iii", $user_id, $product_id, $size_id);
            } else {
                $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND size_id IS NULL");
                $stmt->bind_param("ii", $user_id, $product_id);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($row = $res->fetch_assoc()) {
                // Update quantity
                $new_qty = $row['quantity'] + $qty;
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_qty, $row['id']);
                $stmt->execute();
            } else {
                // Insert new item
                if ($size_id) {
                    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, size_id, quantity, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("iiii", $user_id, $product_id, $size_id, $qty);
                } else {
                    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, size_id, quantity, created_at) VALUES (?, ?, NULL, ?, NOW())");
                    $stmt->bind_param("iii", $user_id, $product_id, $qty);
                }
                $stmt->execute();
            }
        }
        header('Location: cart.php'); exit;
    }
    
    // Other actions
    if ($user_id) {
        if ($action === 'update') {
            if (isset($_POST['qty']) && is_array($_POST['qty'])) {
                foreach ($_POST['qty'] as $cart_id => $q) {
                    $cart_id = intval($cart_id);
                    $q = intval($q);
                    if ($q <= 0) {
                        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
                        $stmt->bind_param("ii", $cart_id, $user_id);
                        $stmt->execute();
                    } else {
                        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
                        $stmt->bind_param("iii", $q, $cart_id, $user_id);
                        $stmt->execute();
                    }
                }
            }
            header('Location: cart.php'); exit;
        }
        
        if ($action === 'remove') {
            $cart_id = intval($_POST['cart_id'] ?? 0);
            if ($cart_id > 0) {
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $cart_id, $user_id);
                $stmt->execute();
            }
            header('Location: cart.php'); exit;
        }
        
        if ($action === 'clear') {
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            header('Location: cart.php'); exit;
        }
    }
}

// Lấy dữ liệu sản phẩm trong giỏ từ DB
$items = [];
$total = 0;
$user_id = get_current_user_id();

if ($user_id) {
    $sql = "SELECT ci.id as cart_id, ci.quantity, ci.size_id, p.id as product_id, p.name, p.price, i.image_url, s.size_name 
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
            LEFT JOIN product_sizes s ON ci.size_id = s.id
            WHERE ci.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['quantity'] * $row['price'];
        $items[] = $row;
        $total += $row['subtotal'];
    }
}
?>
<main class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold mb-4 text-center">Giỏ hàng của bạn</h2>
        <?php if (!$user_id): ?>
            <div class="alert alert-warning text-center">Vui lòng <a href="auth.php" class="fw-bold">đăng nhập</a> để xem và mua hàng.</div>
        <?php elseif (empty($items)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="lead text-muted">Giỏ hàng trống.</p>
                <a href="products.php" class="btn btn-primary-custom px-4">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-0">
                        <form method="post" id="cartForm">
                            <input type="hidden" name="action" value="update">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3">Sản phẩm</th>
                                            <th class="py-3">Size</th>
                                            <th class="py-3">Số lượng</th>
                                            <th class="py-3">Giá</th>
                                            <th class="py-3">Tạm tính</th>
                                            <th class="pe-4 py-3 text-end">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $it): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="/GoodZStore/uploads/<?= htmlspecialchars($it['image_url'] ?? 'no-image.jpg') ?>" alt="<?= htmlspecialchars($it['name']) ?>" class="rounded-3 object-fit-cover" style="width: 60px; height: 60px; margin-right: 15px;">
                                                    <span class="fw-medium"><?= htmlspecialchars($it['name']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($it['size_name']): ?>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($it['size_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="width: 120px;">
                                                <input type="number" name="qty[<?= $it['cart_id'] ?>]" value="<?= $it['quantity'] ?>" min="1" class="form-control text-center">
                                            </td>
                                            <td><?= number_format($it['price'], 0, ',', '.') ?>đ</td>
                                            <td class="fw-bold text-primary"><?= number_format($it['subtotal'], 0, ',', '.') ?>đ</td>
                                            <td class="pe-4 text-end">
                                                <button type="submit" form="removeForm<?= $it['cart_id'] ?>" class="btn btn-link text-danger p-0">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        <!-- Separate forms for removal to avoid nesting -->
                        <?php foreach ($items as $it): ?>
                            <form id="removeForm<?= $it['cart_id'] ?>" method="post" style="display:none;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?= $it['cart_id'] ?>">
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <a href="products.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i> Tiếp tục mua sắm</a>
                    <div>
                        <button type="submit" form="cartForm" class="btn btn-outline-primary me-2">Cập nhật giỏ hàng</button>
                        <form method="post" onsubmit="return confirm('Xóa toàn bộ giỏ hàng?');" style="display:inline-block;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger">Xóa tất cả</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Tổng quan đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tạm tính</span>
                            <span class="fw-bold"><?= number_format($total, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phí vận chuyển</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Tổng cộng</span>
                            <span class="fw-bold fs-5 text-primary"><?= number_format($total, 0, ',', '.') ?>đ</span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary-custom w-100 py-3 fw-bold">Tiến hành thanh toán</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/cart.css">
<script src="../ui.js"></script>