<?php
require_once __DIR__ . '/../../Models/db.php';

// Xử lý CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $type = $_POST['type'];
        $title = $_POST['title'];
        $description = $_POST['description'] ?? '';
        $link_url = $_POST['link_url'] ?? '';
        $button_text = $_POST['button_text'] ?? '';
        $position = intval($_POST['position'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        
        // Upload image
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = __DIR__ . '/../../uploads/';
            $fileName = time() . '_' . $_FILES['image']['name'];
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                $image_url = $fileName;
            }
        }
        
        $sql = "INSERT INTO contents (type, title, description, image_url, link_url, button_text, position, is_active, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $type, $title, $description, $image_url, $link_url, $button_text, $position, $is_active, $start_date, $end_date);
        $stmt->execute();
        
        header('Location: contents.php?success=added');
        exit;
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $type = $_POST['type'];
        $title = $_POST['title'];
        $description = $_POST['description'] ?? '';
        $link_url = $_POST['link_url'] ?? '';
        $button_text = $_POST['button_text'] ?? '';
        $position = intval($_POST['position'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        
        // Upload new image if provided
        $image_url = $_POST['current_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = __DIR__ . '/../../uploads/';
            $fileName = time() . '_' . $_FILES['image']['name'];
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                $image_url = $fileName;
            }
        }
        
        $sql = "UPDATE contents SET type=?, title=?, description=?, image_url=?, link_url=?, button_text=?, position=?, is_active=?, start_date=?, end_date=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssisssi", $type, $title, $description, $image_url, $link_url, $button_text, $position, $is_active, $start_date, $end_date, $id);
        $stmt->execute();
        
        header('Location: contents.php?success=updated');
        exit;
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM contents WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        header('Location: contents.php?success=deleted');
        exit;
    }
}

// Lấy danh sách contents
$contents = [];
$result = $conn->query("SELECT * FROM contents ORDER BY type, position");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $contents[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Banners & Nội dung - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/contents.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                
        <div class="topbar d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
            <h1 class="h2"><i class="fas fa-images"></i> Quản lý Banners & Nội dung</h1>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
                <div class="vr"></div>
                <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
            </div>
        </div>
        <div class="p-4">
            <?php include __DIR__ . '/admin_alerts.php'; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                    $msg = $_GET['success'];
                    echo $msg === 'added' ? 'Thêm thành công!' : 
                        ($msg === 'updated' ? 'Cập nhật thành công!' : 'Xóa thành công!');
                ?>
            </div>
        <?php endif; ?>

        <!-- Banners Section -->
        <div class="content-section">
            <h2><i class="fas fa-image"></i> Banners Trang chủ</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th>Link</th>
                            <th>Vị trí</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contents as $item): ?>
                            <?php if ($item['type'] === 'banner'): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image_url']): ?>
                                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
                                <td><a href="<?= htmlspecialchars($item['link_url']) ?>" target="_blank" class="text-link"><?= $item['button_text'] ?? 'Link' ?></a></td>
                                <td><?= $item['position'] ?></td>
                                <td>
                                    <span class="badge <?= $item['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $item['is_active'] ? 'Hiển thị' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-icon btn-edit" onclick='openEditModal(<?= json_encode($item) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa banner này?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Promos Section -->
        <div class="content-section">
            <h2><i class="fas fa-gift"></i> Banners Khuyến mãi</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th>Link</th>
                            <th>Vị trí</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contents as $item): ?>
                            <?php if ($item['type'] === 'promo'): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image_url']): ?>
                                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
                                <td><a href="<?= htmlspecialchars($item['link_url']) ?>" target="_blank" class="text-link"><?= $item['button_text'] ?? 'Link' ?></a></td>
                                <td><?= $item['position'] ?></td>
                                <td>
                                    <span class="badge <?= $item['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $item['is_active'] ? 'Hiển thị' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-icon btn-edit" onclick='openEditModal(<?= json_encode($item) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa banner này?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Thêm/Sửa -->
    <div id="contentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Thêm Banner mới</h2>
            <form id="contentForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="contentId">
                <input type="hidden" name="current_image" id="currentImage">
                
                <div class="form-group">
                    <label>Loại nội dung</label>
                    <select name="type" id="contentType" required>
                        <option value="banner">Banner trang chủ</option>
                        <option value="promo">Banner khuyến mãi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tiêu đề *</label>
                    <input type="text" name="title" id="contentTitle" required>
                </div>
                
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" id="contentDescription" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Hình ảnh</label>
                    <input type="file" name="image" id="contentImage" accept="image/*">
                    <small>Khuyến nghị: 1200x400px cho banner, 600x300px cho promo</small>
                </div>
                
                <div class="form-group">
                    <label>Link URL</label>
                    <input type="text" name="link_url" id="contentLink" placeholder="/GoodZStore/Views/Users/products.php">
                </div>
                
                <div class="form-group">
                    <label>Text nút (Button)</label>
                    <input type="text" name="button_text" id="contentButton" placeholder="Mua ngay">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Vị trí hiển thị</label>
                        <input type="number" name="position" id="contentPosition" value="0" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="is_active" id="contentActive" checked>
                            Hiển thị
                        </label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày bắt đầu</label>
                        <input type="datetime-local" name="start_date" id="contentStartDate">
                    </div>
                    
                    <div class="form-group">
                        <label>Ngày kết thúc</label>
                        <input type="datetime-local" name="end_date" id="contentEndDate">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Thêm Banner mới';
        document.getElementById('formAction').value = 'add';
        document.getElementById('contentForm').reset();
        document.getElementById('contentModal').style.display = 'block';
    }
    
    function openEditModal(item) {
        document.getElementById('modalTitle').textContent = 'Sửa Banner';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('contentId').value = item.id;
        document.getElementById('currentImage').value = item.image_url || '';
        document.getElementById('contentType').value = item.type;
        document.getElementById('contentTitle').value = item.title;
        document.getElementById('contentDescription').value = item.description || '';
        document.getElementById('contentLink').value = item.link_url || '';
        document.getElementById('contentButton').value = item.button_text || '';
        document.getElementById('contentPosition').value = item.position;
        document.getElementById('contentActive').checked = item.is_active == 1;
        
        // Format datetime for datetime-local input
        if (item.start_date) {
            const startDate = new Date(item.start_date);
            document.getElementById('contentStartDate').value = startDate.toISOString().slice(0, 16);
        }
        if (item.end_date) {
            const endDate = new Date(item.end_date);
            document.getElementById('contentEndDate').value = endDate.toISOString().slice(0, 16);
        }
        
        document.getElementById('contentModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('contentModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('contentModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    </script>

            </main>
        </div>
    </div>
</body>
</html>
