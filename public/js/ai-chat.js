class AIChatWidget {
    constructor() {
        this.widget = this.createWidget();
        this.chatButton = this.createChatButton();
        this.isOpen = false;
        this.initializeEventListeners();
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.id = 'ai-chat-widget';
        widget.className = 'ai-chat-widget';
        widget.innerHTML = `
            <div class="chat-header">
                <div class="header-content">
                    <i class="fas fa-robot"></i>
                    <span>Trợ lý ảo GoodZ</span>
                </div>
                <div class="header-actions">
                    <button class="minimize-btn"><i class="fas fa-minus"></i></button>
                    <button class="close-btn"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div class="message assistant">
                    <div class="message-content">
                        Xin chào! Tôi là trợ lý ảo của GoodZ. Tôi có thể giúp gì cho bạn hôm nay?
                    </div>
                    <div class="suggested-questions">
                        <button class="suggested-question">Có sản phẩm nào đang giảm giá không?</button>
                        <button class="suggested-question">Tư vấn giày thể thao</button>
                        <button class="suggested-question">Kiểm tra đơn hàng</button>
                    </div>
                </div>
            </div>
            <div class="chat-input-container">
                <input type="text" id="chat-input" placeholder="Nhập tin nhắn của bạn...">
                <button id="send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
        `;
        document.body.appendChild(widget);
        return widget;
    }

    createChatButton() {
        const button = document.createElement('button');
        button.id = 'ai-chat-button';
        button.className = 'ai-chat-button';
        button.innerHTML = '<i class="fas fa-comment-dots"></i>';
        document.body.appendChild(button);
        return button;
    }

    initializeEventListeners() {
        // Toggle chat
        this.chatButton.addEventListener('click', () => this.toggleChat());
        this.widget.querySelector('.minimize-btn').addEventListener('click', () => this.toggleChat());
        this.widget.querySelector('.close-btn').addEventListener('click', () => this.toggleChat());

        // Send message
        const sendBtn = this.widget.querySelector('#send-btn');
        const input = this.widget.querySelector('#chat-input');
        
        const sendMessage = () => {
            const message = input.value.trim();
            if (message) {
                this.addMessage('user', message);
                input.value = '';
                this.showTypingIndicator();
                this.sendToAI(message);
            }
        };

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Suggested questions
        this.widget.querySelectorAll('.suggested-question').forEach(button => {
            button.addEventListener('click', (e) => {
                const message = e.target.textContent;
                this.addMessage('user', message);
                this.showTypingIndicator();
                this.sendToAI(message);
            });
        });
    }

    async sendToAI(message) {
        try {
            const response = await fetch('http://localhost:5000/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    user_id: document.body.getAttribute('data-user-id') || 'guest',
                    session_id: this.getSessionId()
                })
            });

            const data = await response.json();
            this.hideTypingIndicator();
            this.addMessage('assistant', data.text, data.suggested_questions);
        } catch (error) {
            console.error('Error:', error);
            this.hideTypingIndicator();
            this.addMessage('assistant', 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.');
        }
    }

    addMessage(sender, text, suggestedQuestions = null) {
        const messagesContainer = this.widget.querySelector('.chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        let messageHTML = `<div class="message-content">${text}</div>`;
        
        if (suggestedQuestions && suggestedQuestions.length > 0) {
            messageHTML += '<div class="suggested-questions">';
            suggestedQuestions.forEach(question => {
                messageHTML += `<button class="suggested-question">${question}</button>`;
            });
            messageHTML += '</div>';
        }
        
        messageDiv.innerHTML = messageHTML;
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
        
        // Add event listeners to new suggested questions
        messageDiv.querySelectorAll('.suggested-question').forEach(button => {
            button.addEventListener('click', (e) => {
                const message = e.target.textContent;
                this.addMessage('user', message);
                this.showTypingIndicator();
                this.sendToAI(message);
            });
        });
    }

    showTypingIndicator() {
        const messagesContainer = this.widget.querySelector('.chat-messages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message assistant typing';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        const typingIndicator = this.widget.querySelector('#typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    scrollToBottom() {
        const messagesContainer = this.widget.querySelector('.chat-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.widget.classList.add('open');
            this.chatButton.style.display = 'none';
            this.widget.querySelector('input').focus();
        } else {
            this.widget.classList.remove('open');
            this.chatButton.style.display = 'flex';
        }
    }

    getSessionId() {
        if (!localStorage.getItem('ai_chat_session')) {
            localStorage.setItem('ai_chat_session', 'session_' + Date.now());
        }
        return localStorage.getItem('ai_chat_session');
    }
}

// Initialize the chat widget when the page loads
document.addEventListener('DOMContentLoaded', () => {
    window.aiChat = new AIChatWidget();
});
