<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
?>
<main>
    <h2>Khuyến mãi & Ưu đãi</h2>
    <div class="promo-list">
        <?php
        $sql = "SELECT code, discount_type, discount_value, start_date, end_date FROM vouchers ORDER BY start_date DESC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $title = $row['discount_type'] === 'percentage'
                    ? ("Giảm ".$row['discount_value']."%")
                    : ("Giảm ".number_format($row['discount_value'], 0, ',', '.')."đ");
                $desc = "Áp dụng: ".date('d/m/Y', strtotime($row['start_date']))." - ".date('d/m/Y', strtotime($row['end_date']));
        ?>
            <div class="promo-card">
                <h3><?= htmlspecialchars($title) ?></h3>
                <p><?= htmlspecialchars($desc) ?></p>
                <div class="promo-code">Mã: <b><?= htmlspecialchars($row['code']) ?></b></div>
            </div>
        <?php endwhile; else: ?>
            <div>Chưa có khuyến mãi.</div>
        <?php endif; ?>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/promotions.css">
<script src="../ui.js"></script>
