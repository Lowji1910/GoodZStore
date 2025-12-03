<?php
session_start();
// Gọi kết nối DB và cấu hình VNPAY
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/vnpay_php/config.php';

$inputData = array();
$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';

// Lấy toàn bộ tham số trả về từ VNPAY (trừ SecureHash)
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}
unset($inputData['vnp_SecureHash']);

// Sắp xếp lại để tạo chữ ký kiểm tra
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$order_id = $_GET['vnp_TxnRef'] ?? 0;
$amount = ($_GET['vnp_Amount'] ?? 0) / 100;

// Kiểm tra chữ ký
if ($secureHash === $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        // --- THANH TOÁN THÀNH CÔNG ---
        
        // Cập nhật trạng thái đơn hàng thành 'processing' (Đã thanh toán/Đang xử lý)
        // Lưu ý: Trong DB của bạn cột status là ENUM('pending','processing'...), 
        // ta dùng 'processing' để biểu thị đã thanh toán thành công.
        $stmt = $conn->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // Chuyển hướng đến trang thông báo thành công
        header("Location: /GoodZStore/Views/Users/order_success.php?order_id=$order_id&status=paid");
        exit;
    } else {
        // --- GIAO DỊCH THẤT BẠI / HỦY ---
        
        // Cập nhật trạng thái đơn hàng thành 'cancelled'
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        header("Location: /GoodZStore/Views/Users/order_success.php?order_id=$order_id&status=failed");
        exit;
    }
} else {
    // --- SAI CHỮ KÝ BẢO MẬT ---
    echo "<h1 style='color:red'>Lỗi bảo mật: Chữ ký không hợp lệ!</h1>";
    echo "<p>Vui lòng liên hệ quản trị viên.</p>";
}
?>