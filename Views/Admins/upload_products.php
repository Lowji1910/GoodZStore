<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];

    // Xử lý ảnh
    $targetDir = "uploads/products/";
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        // Lưu tên file vào DB
        $sql = "INSERT INTO product_images (product_id, image_path, is_main) 
                VALUES ('$product_id', '$fileName', 1)";
        if ($conn->query($sql) === TRUE) {
            echo "✅ Upload thành công!";
        } else {
            echo "❌ Lỗi: " . $conn->error;
        }
    } else {
        echo "❌ Upload ảnh thất bại!";
    }
}
?>

<!-- Form HTML -->
<form action="upload_product.php" method="post" enctype="multipart/form-data">
    <label>Chọn sản phẩm ID:</label>
    <input type="number" name="product_id" required><br><br>

    <label>Chọn ảnh sản phẩm:</label>
    <input type="file" name="image" required><br><br>

    <button type="submit">Upload</button>
</form>
