<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/../header.php';
echo '<link rel="stylesheet" href="../css/order_success.css">';

$order_id = intval($_GET['order_id'] ?? 0);
$status = $_GET['status'] ?? '';

if ($order_id <= 0) {
    echo '<main><h2>Không tìm thấy đơn hàng.</h2></main>';
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
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card order-card p-3">
                <div class="card-body">
                    <h2 class="order-title">Đặt hàng <?= htmlspecialchars($status === 'paid' ? 'thành công' : ($status === 'failed' ? 'thất bại' : 'đã nhận')) ?></h2>
                    <p class="order-meta">Mã đơn hàng: <strong>#<?= $order_id ?></strong></p>
                    <p class="order-meta">Trạng thái:
                        <span id="order-status-badge" class="badge bg-warning text-dark ms-2" style="vertical-align:middle;"><?= htmlspecialchars($order['status'] ?? '') ?></span>
                    </p>

                    <h5 class="mt-3">Chi tiết đơn</h5>
                    <ul class="checkout-items list-unstyled">
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
                            <li class="py-3 d-flex align-items-center border-bottom">
                                <?php if ($image_url): ?>
                                    <img src="/GoodZStore/uploads/<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($product_name) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:6px;margin-right:12px;">
                                <?php else: ?>
                                    <div style="width:64px;height:64px;background:#f3f3f3;border-radius:6px;margin-right:12px;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px;">No image</div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= htmlspecialchars($product_name) ?></div>
                                    <div class="text-muted small">
                                        <?= intval($it['quantity']) ?> x <?= number_format($it['price'],0,',','.') ?>đ
                                        <?php if ($display_size !== ''): ?> &middot; Size: <?= htmlspecialchars($display_size) ?> <?php endif; ?>
                                    </div>
                                </div>
                                <div class="fw-semibold ms-3"><?= number_format(($it['price'] * intval($it['quantity'])),0,',','.') ?>đ</div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="order-summary mt-3">
                        <p>Tiền thanh toán: <strong class="fs-5"><?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>đ</strong></p>
                        <a class="back-home" href="/GoodZStore/Views/Users/index.php">Quay về trang chủ</a>
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
        statusBadgeEl.className = 'badge ms-2';
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
