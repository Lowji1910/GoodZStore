<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';

// Check admin authentication
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../Users/login.php');
    exit;
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_to_training') {
        $conv_id = intval($_POST['conv_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        
        if ($conv_id > 0 && $label) {
            // Get conversation data
            $stmt = $conn->prepare("SELECT message, metadata FROM ai_conversations WHERE id = ?");
            $stmt->bind_param('i', $conv_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $conv = $result->fetch_assoc();
            
            if ($conv) {
                // Add to training data
                $text = json_encode([
                    'message' => $conv['message'],
                    'metadata' => $conv['metadata']
                ]);
                
                $stmt = $conn->prepare("INSERT INTO ai_training_data (source, ref_id, text, label) VALUES ('conversation', ?, ?, ?)");
                $stmt->bind_param('iss', $conv_id, $text, $label);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Đã thêm vào dữ liệu huấn luyện!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($stmt->error) . '</div>';
                }
            }
        }
    } elseif ($action === 'delete_training') {
        $training_id = intval($_POST['training_id'] ?? 0);
        if ($training_id > 0) {
            $stmt = $conn->prepare("DELETE FROM ai_training_data WHERE id = ?");
            $stmt->bind_param('i', $training_id);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Đã xóa dữ liệu huấn luyện!</div>';
            }
        }
    }
}

// Pagination for conversations
$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total conversations
$total_result = $conn->query("SELECT COUNT(*) as total FROM ai_conversations WHERE direction='user'");
$total_row = $total_result->fetch_assoc();
$total_conversations = $total_row['total'];
$total_pages = ceil($total_conversations / $limit);

// Get conversations
$conversations_sql = "SELECT c.*, u.full_name 
                      FROM ai_conversations c
                      LEFT JOIN users u ON c.user_id = u.id
                      WHERE c.direction = 'user'
                      ORDER BY c.created_at DESC
                      LIMIT ? OFFSET ?";
$stmt = $conn->prepare($conversations_sql);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$conversations = $stmt->get_result();

// Get training data statistics
$stats_sql = "SELECT label, COUNT(*) as count FROM ai_training_data GROUP BY label";
$stats_result = $conn->query($stats_sql);
$stats = [];
while ($row = $stats_result->fetch_assoc()) {
    $stats[$row['label'] ?? 'unlabeled'] = $row['count'];
}

// Get recent training data
$training_sql = "SELECT * FROM ai_training_data ORDER BY created_at DESC LIMIT 50";
$training_data = $conn->query($training_sql);

include_once __DIR__ . '/admin_sidebar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý AI Training - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .container {
            margin-left: 250px;
            padding: 2rem;
        }
        
        h1, h2 {
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #2196f3;
        }
        
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #ddd;
        }
        
        .tab {
            padding: 0.8rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #2196f3;
            border-bottom-color: #2196f3;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1976d2;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
        }
        
        .btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-info {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .badge-warning {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover {
            background: #f5f5f5;
        }
        
        .pagination .active {
            background: #2196f3;
            color: white;
            border-color: #2196f3;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-brain"></i> Quản lý AI Training Data</h1>
        
        <?= $message ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Tổng hội thoại</h3>
                <div class="number"><?= number_format($total_conversations) ?></div>
            </div>
            <div class="stat-card">
                <h3>Dữ liệu huấn luyện</h3>
                <div class="number"><?= number_format(array_sum($stats)) ?></div>
            </div>
            <?php foreach ($stats as $label => $count): ?>
            <div class="stat-card">
                <h3><?= htmlspecialchars(ucfirst($label)) ?></h3>
                <div class="number"><?= number_format($count) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('conversations')">
                <i class="fas fa-comments"></i> Hội thoại
            </button>
            <button class="tab" onclick="switchTab('training')">
                <i class="fas fa-database"></i> Dữ liệu huấn luyện
            </button>
        </div>
        
        <!-- Conversations Tab -->
        <div id="conversations" class="tab-content active">
            <div class="table-container">
                <h2>Hội thoại người dùng</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Tin nhắn</th>
                            <th>Session</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($conv = $conversations->fetch_assoc()): ?>
                        <tr>
                            <td><?= $conv['id'] ?></td>
                            <td><?= htmlspecialchars($conv['full_name'] ?? 'Guest') ?></td>
                            <td>
                                <?= htmlspecialchars(substr($conv['message'], 0, 100)) ?>
                                <?php if (strlen($conv['message']) > 100): ?>...<?php endif; ?>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars(substr($conv['session_id'], 0, 12)) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($conv['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="openTrainingModal(<?= $conv['id'] ?>, '<?= htmlspecialchars(addslashes($conv['message'])) ?>')">
                                    <i class="fas fa-plus"></i> Thêm vào Training
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i> Trước</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>">Sau <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Training Data Tab -->
        <div id="training" class="tab-content">
            <div class="table-container">
                <h2>Dữ liệu huấn luyện</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nguồn</th>
                            <th>Label</th>
                            <th>Nội dung</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($training = $training_data->fetch_assoc()): ?>
                        <tr>
                            <td><?= $training['id'] ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($training['source']) ?></span></td>
                            <td>
                                <?php
                                $label = $training['label'] ?? 'unlabeled';
                                $badgeClass = $label === 'unlabeled' ? 'badge-warning' : 'badge-success';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($label) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars(substr($training['text'], 0, 80)) ?>
                                <?php if (strlen($training['text']) > 80): ?>...<?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($training['created_at'])) ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                    <input type="hidden" name="action" value="delete_training">
                                    <input type="hidden" name="training_id" value="<?= $training['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal for adding to training -->
    <div id="trainingModal" class="modal">
        <div class="modal-content">
            <h2>Thêm vào dữ liệu huấn luyện</h2>
            <form method="post">
                <input type="hidden" name="action" value="add_to_training">
                <input type="hidden" name="conv_id" id="modal_conv_id">
                
                <div class="form-group">
                    <label>Tin nhắn:</label>
                    <textarea id="modal_message" rows="3" readonly></textarea>
                </div>
                
                <div class="form-group">
                    <label>Label (phân loại):</label>
                    <select name="label" required>
                        <option value="">-- Chọn label --</option>
                        <option value="recommend">Recommend (Gợi ý sản phẩm)</option>
                        <option value="ask_size">Ask Size (Hỏi về size)</option>
                        <option value="promo">Promo (Khuyến mãi)</option>
                        <option value="general">General (Câu hỏi chung)</option>
                        <option value="style_advice">Style Advice (Tư vấn phối đồ)</option>
                    </select>
                </div>
                
                <div style="display:flex; gap:1rem; justify-content:flex-end;">
                    <button type="button" class="btn" onclick="closeTrainingModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        function openTrainingModal(convId, message) {
            document.getElementById('modal_conv_id').value = convId;
            document.getElementById('modal_message').value = message;
            document.getElementById('trainingModal').classList.add('active');
        }
        
        function closeTrainingModal() {
            document.getElementById('trainingModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        document.getElementById('trainingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTrainingModal();
            }
        });
    </script>
</body>
</html>
