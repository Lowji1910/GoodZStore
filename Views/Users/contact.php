<?php
include_once __DIR__ . '/../header.php';
?>
<main>
    <h2>Liên hệ GoodZStore</h2>
    <section class="contact-section">
        <p>Địa chỉ: 123 Đường Thời Trang, Quận 1, TP.HCM</p>
        <p>Email: support@goodzstore.com</p>
        <p>Hotline: 0123 456 789</p>
        <form class="contact-form">
            <label for="name">Họ tên:</label>
            <input type="text" id="name" name="name" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="message">Nội dung:</label>
            <textarea id="message" name="message" rows="4" required></textarea>
            <button type="submit">Gửi liên hệ</button>
        </form>
    </section>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/contact.css">
<script src="../ui.js"></script>
