-- Migration: Create AI-related tables for GoodZStore
-- Created: 2024
-- Description: Tables for AI chatbot conversations and training data

USE goodzstore;

-- Drop tables if they exist (for clean migration)
DROP TABLE IF EXISTS ai_training_data;
DROP TABLE IF EXISTS ai_conversations;

-- Table: ai_conversations
-- Purpose: Store all chat conversations between users and AI
CREATE TABLE ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT 'Reference to users table, NULL for guest users',
    session_id VARCHAR(100) NOT NULL COMMENT 'Unique session identifier for grouping conversations',
    direction ENUM('user', 'bot') NOT NULL COMMENT 'Message direction: user or bot',
    intent VARCHAR(50) NULL COMMENT 'Detected intent (e.g., size_suggest, recommend, promo)',
    message TEXT NOT NULL COMMENT 'The actual message content',
    metadata JSON NULL COMMENT 'Additional data like product_id, measurements, etc.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the message was created',
    
    -- Indexes for performance
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_direction (direction),
    INDEX idx_created (created_at),
    INDEX idx_intent (intent),
    
    -- Foreign key constraint (optional, depends on your users table)
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores AI chatbot conversation history';

-- Table: ai_training_data
-- Purpose: Store curated data for training/fine-tuning the AI model
CREATE TABLE ai_training_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(50) NOT NULL COMMENT 'Source of data: conversation, size_tool, manual, etc.',
    ref_id INT NULL COMMENT 'Reference ID to source (e.g., conversation_id)',
    text TEXT NOT NULL COMMENT 'The training text/data in JSON or plain text format',
    label VARCHAR(50) NULL COMMENT 'Classification label: recommend, ask_size, promo, general, style_advice',
    is_validated BOOLEAN DEFAULT FALSE COMMENT 'Whether this data has been validated by admin',
    quality_score TINYINT NULL COMMENT 'Quality score 1-5, set by admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the data was added',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
    
    -- Indexes for performance
    INDEX idx_source (source),
    INDEX idx_label (label),
    INDEX idx_validated (is_validated),
    INDEX idx_created (created_at),
    
    -- Full-text search index for text content
    FULLTEXT INDEX ft_text (text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Curated training data for AI model improvement';

-- Insert sample data for testing
-- Sample conversation
INSERT INTO ai_conversations (user_id, session_id, direction, intent, message, metadata) VALUES
(NULL, 'ses-sample-001', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{"product_id": 1, "height_cm": 170, "weight_kg": 65}'),
(NULL, 'ses-sample-001', 'bot', 'size_suggest', 'Với chiều cao 170cm và cân nặng 65kg, mình gợi ý bạn nên chọn size M. Size này sẽ vừa vặn và thoải mái cho bạn.', '{"size_suggestion": "M", "reason": "Based on height 170cm"}'),
(NULL, 'ses-sample-002', 'user', NULL, 'Có mã giảm giá nào không?', '{"product_id": 2}'),
(NULL, 'ses-sample-002', 'bot', 'promo', 'Hiện tại shop đang có mã SUMMER2024 giảm 20% cho đơn hàng từ 500k. Bạn có thể áp dụng khi thanh toán nhé!', NULL);

-- Sample training data
INSERT INTO ai_training_data (source, ref_id, text, label, is_validated, quality_score) VALUES
('conversation', 1, '{"user": "Tôi cao 170cm, nên mặc size nào?", "bot": "Với chiều cao 170cm, mình gợi ý size M", "metadata": {"height_cm": 170}}', 'ask_size', TRUE, 5),
('conversation', 3, '{"user": "Có mã giảm giá nào không?", "bot": "Hiện tại shop đang có mã SUMMER2024 giảm 20%"}', 'promo', TRUE, 5),
('manual', NULL, '{"user": "Áo này phối với quần gì đẹp?", "bot": "Áo này bạn có thể phối với quần jean hoặc quần kaki đều đẹp"}', 'style_advice', TRUE, 4),
('manual', NULL, '{"user": "Sản phẩm này có màu nào khác không?", "bot": "Sản phẩm này hiện có 3 màu: đen, trắng và xám"}', 'general', TRUE, 4);

-- Create view for easy querying of conversation threads
CREATE OR REPLACE VIEW v_conversation_threads AS
SELECT 
    c.session_id,
    c.user_id,
    u.full_name as user_name,
    COUNT(*) as message_count,
    MIN(c.created_at) as started_at,
    MAX(c.created_at) as last_message_at,
    GROUP_CONCAT(
        CONCAT(c.direction, ': ', SUBSTRING(c.message, 1, 50))
        ORDER BY c.created_at
        SEPARATOR ' | '
    ) as conversation_preview
FROM ai_conversations c
LEFT JOIN users u ON c.user_id = u.id
GROUP BY c.session_id, c.user_id, u.full_name;

-- Create view for training data statistics
CREATE OR REPLACE VIEW v_training_stats AS
SELECT 
    label,
    COUNT(*) as total_count,
    SUM(CASE WHEN is_validated = TRUE THEN 1 ELSE 0 END) as validated_count,
    AVG(quality_score) as avg_quality,
    MIN(created_at) as first_added,
    MAX(created_at) as last_added
FROM ai_training_data
GROUP BY label;

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE ON goodzstore.ai_conversations TO 'your_app_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON goodzstore.ai_training_data TO 'your_app_user'@'localhost';

-- Success message
SELECT 'AI tables created successfully!' as status,
       (SELECT COUNT(*) FROM ai_conversations) as sample_conversations,
       (SELECT COUNT(*) FROM ai_training_data) as sample_training_data;
