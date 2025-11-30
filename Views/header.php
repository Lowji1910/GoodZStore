<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoodZStore - Thời trang chính hãng</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/header.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/ai_chat.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg fixed-top glass-effect">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="/GoodZStore/Views/Users/index.php">
            <span class="fw-bold fs-4 text-dark">GoodZ<span class="text-warning">Store</span></span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item">
                    <a class="nav-link" href="/GoodZStore/Views/Users/index.php">Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Danh mục</a>
                    <ul class="dropdown-menu border-0 shadow-lg rounded-3 mt-2">
                        <?php
                        require_once __DIR__ . '/../Models/db.php';
                        $sql = "SELECT * FROM categories ORDER BY name";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0):
                            while ($category = $result->fetch_assoc()): ?>
                                <li><a class="dropdown-item py-2" href="/GoodZStore/Views/Users/category.php?id=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                            <?php endwhile;
                        endif; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="/GoodZStore/Views/Users/products.php">Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="/GoodZStore/Views/Users/about.php">Giới thiệu</a></li>
                <li class="nav-item"><a class="nav-link" href="/GoodZStore/Views/Users/contact.php">Liên hệ</a></li>
            </ul>

            <!-- Icons -->
            <div class="d-flex align-items-center gap-3">
                <!-- Search -->
                <div class="position-relative d-none d-lg-block">
                    <input type="text" class="form-control rounded-pill pe-5" placeholder="Tìm kiếm..." style="width: 200px; font-size: 0.9rem;" onkeydown="if(event.key==='Enter') window.location.href='/GoodZStore/Views/Users/products.php?q='+encodeURIComponent(this.value)">
                    <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>

                <!-- Cart -->
                <?php
                require_once __DIR__ . '/../Models/cart_functions.php';
                $cart_count = getCartItemCount();
                ?>
                <a href="/GoodZStore/Views/Users/cart.php" class="position-relative text-dark fs-5">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.6rem;">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Notifications -->
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <a href="#" class="text-dark fs-5 position-relative" id="userNotiBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="far fa-bell"></i>
                            <span id="userNotiBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; display:none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 mt-2" aria-labelledby="userNotiBtn" style="min-width:320px; max-height:400px; overflow-y:auto;">
                            <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                Thông báo
                                <button id="markAllReadUser" class="btn btn-link btn-sm text-decoration-none p-0" style="font-size:0.8rem;">Đã đọc tất cả</button>
                            </h6></li>
                            <div id="userNotiList">
                                <li class="px-3 py-2 text-muted text-center">Đang tải...</li>
                            </div>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- User -->
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown">
                            <i class="far fa-user-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 mt-2">
                            <li><h6 class="dropdown-header">Xin chào, <?= htmlspecialchars($_SESSION['user']['full_name']) ?></h6></li>
                            <li><a class="dropdown-item" href="/GoodZStore/Views/Users/profile.php"><i class="fas fa-id-card me-2"></i>Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="/GoodZStore/Views/Users/orders.php"><i class="fas fa-box me-2"></i>Đơn hàng</a></li>
                            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-primary" href="/GoodZStore/Views/Admins/admin_dashboard.php"><i class="fas fa-user-shield me-2"></i>Quản trị viên</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/GoodZStore/Views/Users/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/GoodZStore/Views/Users/auth.php" class="btn btn-primary-custom btn-sm">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer for fixed header -->
<div style="height: 80px;"></div>

<!-- AI Chat Widget -->
<button id="chatbot-btn" title="Chat với AI">
    <i class="fas fa-robot"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Notifications Logic
    const notiBadge = document.getElementById('userNotiBadge');
    const notiList = document.getElementById('userNotiList');
    const markAllBtn = document.getElementById('markAllReadUser');

    if (notiBadge && notiList) {
        async function fetchUserNotifications() {
            try {
                const res = await fetch('/GoodZStore/Views/Users/notifications_api.php?limit=10', {cache: 'no-store'});
                const data = await res.json();
                
                if (data.unread && data.unread > 0) {
                    notiBadge.style.display = 'inline-block';
                    notiBadge.textContent = data.unread;
                } else {
                    notiBadge.style.display = 'none';
                }

                notiList.innerHTML = '';
                if (data.items && data.items.length) {
                    data.items.forEach(it => {
                        const li = document.createElement('li');
                        li.className = 'dropdown-item p-2 border-bottom';
                        li.style.whiteSpace = 'normal';
                        if (it.is_read == 0) li.style.backgroundColor = '#f0f8ff';

                        li.innerHTML = `
                            <div style="cursor:pointer;" onclick="markUserNotiRead(${it.id}, '${it.link || ''}')">
                                <div class="fw-bold small">${it.type}</div>
                                <div class="small text-dark">${it.message}</div>
                                <small class="text-muted" style="font-size:0.7rem;">${it.created_at}</small>
                            </div>
                        `;
                        notiList.appendChild(li);
                    });
                } else {
                    notiList.innerHTML = '<li class="dropdown-item text-muted text-center small">Không có thông báo mới</li>';
                }
            } catch (e) {
                console.error('Noti Error:', e);
            }
        }

        // Initial fetch and interval
        fetchUserNotifications();
        setInterval(fetchUserNotifications, 15000);

        // Mark all read
        if (markAllBtn) {
            markAllBtn.addEventListener('click', async (e) => {
                e.stopPropagation(); // Prevent dropdown close
                await fetch('/GoodZStore/Views/Users/notifications_api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=mark_all_read'
                });
                fetchUserNotifications();
            });
        }
        
        // Global function for onclick
        window.markUserNotiRead = async function(id, link) {
            try {
                await fetch('/GoodZStore/Views/Users/notifications_api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=mark_one_read&id=' + id
                });
                
                // Update Badge immediately
                let count = parseInt(notiBadge.textContent) || 0;
                if (count > 0) {
                    count--;
                    notiBadge.textContent = count;
                    if (count === 0) notiBadge.style.display = 'none';
                }

                if (link) window.location.href = link;
                else fetchUserNotifications();
            } catch (e) { console.error(e); }
        };
    }
});
</script>