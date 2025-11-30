<?php
// Views/Admins/admin_alerts.php
// Hiển thị thông báo flash message từ GET param hoặc biến $msg

$alert_msg = '';
$alert_type = 'success';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added': $alert_msg = 'Thêm mới thành công!'; break;
        case 'updated': $alert_msg = 'Cập nhật thành công!'; break;
        case 'deleted': $alert_msg = 'Xóa thành công!'; break;
        case 'error': $alert_msg = 'Có lỗi xảy ra!'; $alert_type = 'danger'; break;
        default: $alert_msg = htmlspecialchars($_GET['msg']); break;
    }
} elseif (!empty($msg)) {
    $alert_msg = $msg;
    $alert_type = 'danger'; // Mặc định $msg dùng cho lỗi
}

if ($alert_msg):
?>
<div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
    <?= $alert_msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
