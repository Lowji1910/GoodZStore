<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/../header.php';

$order_id = intval($_GET['order_id'] ?? 0);
$status = $_GET['status'] ?? '';

if ($order_id <= 0) {
    echo '<main class="container py-5 text-center">
            <div class="py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h2 class="fw-bold">Không tìm thấy đơn hàng</h2>
                <a href="index.php" class="btn btn-primary-custom mt-3">Về trang chủ</a>
            </div>
          </main>';
    include_once __DIR__ . '/../footer.php';
    exit;
}

$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();

// Fetch order items
$items = [];
$stmt2 = $conn->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt2->bind_param('i', $order_id);
$stmt2->execute();
$ri = $stmt2->get_result();
while ($r = $ri->fetch_assoc()) $items[] = $r;

// Prepare product query to get product name and main image
$pstmt = $conn->prepare('SELECT p.name, p.slug, i.image_url FROM products p LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 WHERE p.id = ? LIMIT 1');

?>
<main class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div class="text-center mb-5">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="fw-bold mb-2">Đặt hàng <?= htmlspecialchars($status === 'paid' ? 'thành công' : ($status === 'failed' ? 'thất bại' : 'đã nhận')) ?>!</h2>
                    <p class="text-muted">Cảm ơn bạn đã mua sắm tại GoodZStore.</p>
                </div>

                <!-- Order Details Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Mã đơn hàng</span>
                            <h5 class="fw-bold mb-0">#<?= $order_id ?></h5>
                        </div>
                        <span id="order-status-badge" class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                            <?= htmlspecialchars($order['status'] ?? 'Pending') ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Chi tiết sản phẩm</h6>
                        <div class="list-group list-group-flush mb-4 rounded-3">
                            <?php foreach ($items as $it): 
                                    // Lookup product metadata
                                    $product_name = 'Sản phẩm #' . intval($it['product_id']);
                                    $image_url = null;
                                    if ($pstmt) {
                                            $pid = intval($it['product_id']);
                                            $pstmt->bind_param('i', $pid);
                                            $pstmt->execute();
                                            $pres = $pstmt->get_result();
                                            if ($prow = $pres->fetch_assoc()) {
                                                    $product_name = $prow['name'] ?: $product_name;
                                                    $image_url = $prow['image_url'];
                                            }
                                    }
                                    $display_size = trim($it['size_name'] ?? '');
                            ?>
                                <div class="list-group-item border-0 d-flex align-items-center py-3 px-0 border-bottom">
                                    <?php if ($image_url): ?>
                                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($product_name) ?>" class="rounded-3 object-fit-cover" style="width:64px;height:64px;">
                                    <?php else: ?>
                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted small" style="width:64px;height:64px;">No IMG</div>
                                    <?php endif; ?>
                                    
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($product_name) ?></h6>
                                        <div class="text-muted small">
                                            <?= intval($it['quantity']) ?> x <?= number_format($it['price'],0,',','.') ?>đ
                                            <?php if ($display_size !== ''): ?> <span class="mx-1">&middot;</span> Size: <?= htmlspecialchars($display_size) ?> <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="fw-bold"><?= number_format(($it['price'] * intval($it['quantity'])),0,',','.') ?>đ</div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Could add shipping info here if available in $order -->
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tổng tiền</span>
                                    <span class="fw-bold fs-5 text-primary"><?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 p-4 text-center">
                        <a href="/GoodZStore/Views/Users/index.php" class="btn btn-primary-custom px-5 py-2 fw-bold">
                            <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                        </a>
                        <div class="mt-3">
                            <a href="/GoodZStore/Views/Users/orders.php" class="text-decoration-none text-muted small">Xem lịch sử đơn hàng</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Poll order status every 8 seconds and update badge/text
(function(){
    const orderId = <?= json_encode($order_id) ?>;
    const statusBadgeEl = document.getElementById('order-status-badge');
    let lastStatus = statusBadgeEl ? statusBadgeEl.textContent.trim() : null;

    function setBadge(status) {
        if (!statusBadgeEl) return;
        statusBadgeEl.textContent = status;
        statusBadgeEl.className = 'badge rounded-pill px-3 py-2 ms-2'; // Keep base classes
        switch((status||'').toLowerCase()){
            case 'pending': statusBadgeEl.classList.add('bg-warning','text-dark'); break;
            case 'processing': statusBadgeEl.classList.add('bg-info','text-white'); break;
            case 'shipped': statusBadgeEl.classList.add('bg-primary','text-white'); break;
            case 'completed': statusBadgeEl.classList.add('bg-success','text-white'); break;
            case 'cancelled': statusBadgeEl.classList.add('bg-danger','text-white'); break;
            default: statusBadgeEl.classList.add('bg-secondary','text-white'); break;
        }
    }

    async function pollStatus(){
        try{
            const res = await fetch('order_status_api.php?order_id=' + encodeURIComponent(orderId));
            if (!res.ok) return;
            const j = await res.json();
            const st = j.status || '';
            if (st !== lastStatus) {
                lastStatus = st;
                setBadge(st);
            }
            // stop polling when final states reached
            if (['completed','cancelled','shipped'].includes((st||'').toLowerCase())) return;
        } catch (e) {
            // ignore network errors
        }
        setTimeout(pollStatus, 8000);
    }

    // initialize badge style
    setBadge(lastStatus);
    // start polling
    setTimeout(pollStatus, 2000);
})();
</script>

<?php include_once __DIR__ . '/../footer.php'; ?>
