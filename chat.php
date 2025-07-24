<?php
session_start();
require_once 'config/database.php';
require_once 'fungsi.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'] ?? '';

// Get selected conversation
$selectedUserId = $_GET['user_id'] ?? null;
$selectedUser = null;

if ($selectedUserId) {
    $stmt = $pdo->prepare("SELECT id, fullname, role, store_name FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get conversations with unread count
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = ? THEN m.receiver_id 
            ELSE m.sender_id 
        END as other_user_id,
        u.fullname as other_user_name,
        u.role as other_user_role,
        u.store_name,
        MAX(m.created_at) as last_message_time,
        (SELECT message FROM messages m2 
         WHERE (m2.sender_id = ? AND m2.receiver_id = other_user_id) 
            OR (m2.sender_id = other_user_id AND m2.receiver_id = ?)
         ORDER BY m2.created_at DESC LIMIT 1) as last_message,
        (SELECT COUNT(*) FROM messages m3 
         WHERE m3.sender_id = other_user_id AND m3.receiver_id = ? AND m3.is_read = 0) as unread_count
    FROM messages m
    JOIN users u ON u.id = CASE 
        WHEN m.sender_id = ? THEN m.receiver_id 
        ELSE m.sender_id 
    END
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
    GROUP BY other_user_id, u.fullname, u.role, u.store_name
    ORDER BY last_message_time DESC
");
$stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get messages for selected conversation
$messages = [];
if ($selectedUserId) {
    $stmt = $pdo->prepare("
        UPDATE messages SET is_read = 1 
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$selectedUserId, $currentUserId]);
    
    $stmt = $pdo->prepare("
        SELECT m.*, u.fullname as sender_name 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$currentUserId, $selectedUserId, $selectedUserId, $currentUserId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - KreasiDB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            height: 100vh;
            overflow: hidden;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1000;
        }
        
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .navbar .user-info {
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .navbar .user-info a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .navbar .user-info a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .chat-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: calc(100vh - 80px);
        }
        
        .conversations-panel {
            background: white;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }
        
        .conversations-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }
        
        .conversations-header h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 0.9rem;
            outline: none;
        }
        
        .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102,126,234,0.1);
        }
        
        .search-box i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f1f1;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
            position: relative;
        }
        
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        
        .conversation-item.active {
            background-color: #667eea;
            color: white;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .conversation-info {
            flex: 1;
            min-width: 0;
        }
        
        .conversation-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .conversation-preview {
            font-size: 0.85rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-item.active .conversation-preview {
            color: rgba(255,255,255,0.8);
        }
        
        .conversation-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }
        
        .conversation-time {
            font-size: 0.75rem;
            color: #999;
        }
        
        .conversation-item.active .conversation-time {
            color: rgba(255,255,255,0.7);
        }
        
        .unread-badge {
            background: #dc3545;
            color: white;
            border-radius: 10px;
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }
        
        .conversation-item.active .unread-badge {
            background: rgba(255,255,255,0.8);
            color: #667eea;
        }
        
        .chat-panel {
            display: flex;
            flex-direction: column;
            background: white;
        }
        
        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .chat-header .user-avatar {
            width: 50px;
            height: 50px;
        }
        
        .chat-user-info h3 {
            margin-bottom: 0.25rem;
            color: #333;
        }
        
        .chat-user-info .role {
            font-size: 0.85rem;
            color: #666;
            text-transform: capitalize;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.75rem;
        }
        
        .message.sent {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .message.sent .message-avatar {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .message-content {
            max-width: 70%;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .message.sent .message-content {
            background: #667eea;
            color: white;
        }
        
        .message-text {
            margin-bottom: 0.25rem;
            word-wrap: break-word;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #999;
        }
        
        .message.sent .message-time {
            color: rgba(255,255,255,0.7);
        }
        
        .chat-input {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
            background: white;
        }
        
        .input-group {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
        }
        
        .input-group textarea {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
            outline: none;
            font-family: inherit;
            font-size: 0.9rem;
            max-height: 120px;
            min-height: 45px;
        }
        
        .input-group textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102,126,234,0.1);
        }
        
        .send-btn {
            width: 45px;
            height: 45px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        
        .send-btn:hover {
            background: #5a6fd8;
        }
        
        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #ccc transparent;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
        
        .typing-indicator {
            padding: 1rem;
            font-style: italic;
            color: #666;
            font-size: 0.85rem;
        }

        .online-indicator {
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 2px;
            right: 2px;
        }
        
        @media (max-width: 768px) {
            .chat-container {
                grid-template-columns: 1fr;
            }
            
            .conversations-panel {
                display: none;
            }
            
            .chat-panel.mobile-hidden {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1><i class="fas fa-comments"></i> KreasiDB Chat</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></span>
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="chat-container">
        <!-- Conversations Panel -->
        <div class="conversations-panel">
            <div class="conversations-header">
                <h3>Pesan</h3>
                <div class="search-box">
                    <input type="text" id="searchUsers" placeholder="Cari pengguna...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="conversations-list scrollbar-thin">
                <?php if (empty($conversations)): ?>
                    <div style="padding: 2rem; text-align: center; color: #666;">
                        <i class="fas fa-comments" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: #ddd;"></i>
                        Belum ada percakapan
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <a href="?user_id=<?php echo $conv['other_user_id']; ?>" 
                           class="conversation-item <?php echo ($selectedUserId == $conv['other_user_id']) ? 'active' : ''; ?>">
                            <div class="user-avatar" style="position: relative;">
                                <?php echo strtoupper(substr($conv['other_user_name'], 0, 1)); ?>
                                <div class="online-indicator"></div>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name">
                                    <?php echo htmlspecialchars($conv['other_user_name']); ?>
                                    <?php if ($conv['store_name']): ?>
                                        <small>(<?php echo htmlspecialchars($conv['store_name']); ?>)</small>
                                    <?php endif; ?>
                                </div>
                                <div class="conversation-preview">
                                    <?php 
                                    if ($conv['last_message']) {
                                        echo htmlspecialchars(substr($conv['last_message'], 0, 40)) . 
                                             (strlen($conv['last_message']) > 40 ? '...' : '');
                                    } else {
                                        echo 'Mulai percakapan...';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="conversation-meta">
                                <?php if ($conv['last_message_time']): ?>
                                    <div class="conversation-time">
                                        <?php 
                                        $time = new DateTime($conv['last_message_time']);
                                        $now = new DateTime();
                                        $diff = $now->diff($time);
                                        
                                        if ($diff->days == 0) {
                                            echo $time->format('H:i');
                                        } elseif ($diff->days == 1) {
                                            echo 'Kemarin';
                                        } else {
                                            echo $time->format('d/m');
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($conv['unread_count']) && $conv['unread_count'] > 0): ?>
                                    <div class="unread-badge"><?php echo $conv['unread_count']; ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Panel -->
        <div class="chat-panel">
            <?php if ($selectedUser): ?>
                <div class="chat-header">
                    <div class="user-avatar" style="position: relative;">
                        <?php echo strtoupper(substr($selectedUser['fullname'], 0, 1)); ?>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="chat-user-info">
                        <h3><?php echo htmlspecialchars($selectedUser['fullname']); ?></h3>
                        <div class="role">
                            <?php echo htmlspecialchars($selectedUser['role']); ?>
                            <?php if ($selectedUser['store_name']): ?>
                                - <?php echo htmlspecialchars($selectedUser['store_name']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="chatMessages" class="chat-messages scrollbar-thin">
                    <?php if (empty($messages)): ?>
                        <div class="empty-state">
                            <i class="fas fa-comment-dots"></i>
                            <h3>Mulai percakapan</h3>
                            <p>Kirim pesan pertama Anda ke <?php echo htmlspecialchars($selectedUser['fullname']); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <?php 
                            $isSent = ($message['sender_id'] == $currentUserId);
                            $messageClass = $isSent ? 'sent' : 'received';
                            $senderInitial = strtoupper(substr($message['sender_name'], 0, 1));
                            
                            $time = new DateTime($message['created_at']);
                            $timeStr = $time->format('H:i');
                            ?>
                            <div class="message <?php echo $messageClass; ?>">
                                <div class="message-avatar"><?php echo $senderInitial; ?></div>
                                <div class="message-content">
                                    <div class="message-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                    <div class="message-time"><?php echo $timeStr; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="chat-input">
                    <form id="messageForm" class="input-group">
                        <textarea id="messageInput" 
                                placeholder="Ketik pesan..." 
                                rows="1" 
                                required
                                maxlength="1000"></textarea>
                        <button type="submit" class="send-btn" id="sendButton">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Pilih percakapan</h3>
                    <p>Pilih percakapan dari daftar untuk mulai mengobrol</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let currentUserId = <?php echo $currentUserId; ?>;
        let selectedUserId = <?php echo $selectedUserId ?: 'null'; ?>;
        let messageCheckInterval;
        let lastMessageCount = 0;

        // Auto-resize textarea
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Handle Enter key
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('messageForm').dispatchEvent(new Event('submit'));
                }
            });
        }

        // Send message
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const message = messageInput.value.trim();
                if (!message || !selectedUserId) return;
                
                const sendButton = document.getElementById('sendButton');
                sendButton.disabled = true;
                
                fetch('api/send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        messageInput.style.height = 'auto';
                        loadMessages();
                        updateConversationList();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim pesan');
                })
                .finally(() => {
                    sendButton.disabled = false;
                });
            });
        }

        // Load messages
        function loadMessages() {
            if (!selectedUserId) return;
            
            fetch(`api/get_messages.php?user_id=${selectedUserId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('chatMessages').innerHTML = data.html;
                        scrollToBottom();
                        
                        // Update last message count
                        if (data.count > lastMessageCount) {
                            lastMessageCount = data.count;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
        }

        // Scroll to bottom
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Update conversation list
        function updateConversationList() {
            // This would require additional API endpoint to refresh conversation list
            // For now, we'll reload the page periodically or implement WebSocket
        }

        // Search functionality
        const searchInput = document.getElementById('searchUsers');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const conversations = document.querySelectorAll('.conversation-item');
                
                conversations.forEach(conv => {
                    const name = conv.querySelector('.conversation-name').textContent.toLowerCase();
                    const preview = conv.querySelector('.conversation-preview').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || preview.includes(searchTerm)) {
                        conv.style.display = 'flex';
                    } else {
                        conv.style.display = 'none';
                    }
                });
            });
        }

        // Auto-refresh messages
        function startMessagePolling() {
            if (selectedUserId) {
                messageCheckInterval = setInterval(() => {
                    loadMessages();
                }, 3000); // Check every 3 seconds
            }
        }

        function stopMessagePolling() {
            if (messageCheckInterval) {
                clearInterval(messageCheckInterval);
                messageCheckInterval = null;
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            if (selectedUserId) {
                scrollToBottom();
                startMessagePolling();
                
                // Get initial message count
                const messages = document.querySelectorAll('.message');
                lastMessageCount = messages.length;
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopMessagePolling();
        });

        // Handle visibility change (pause polling when tab is not active)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopMessagePolling();
            } else if (selectedUserId) {
                startMessagePolling();
                loadMessages(); // Immediately check for new messages
            }
        });

        // Mobile responsiveness
        function handleMobileView() {
            const isMobile = window.innerWidth <= 768;
            const conversationsPanel = document.querySelector('.conversations-panel');
            const chatPanel = document.querySelector('.chat-panel');
            
            if (isMobile) {
                if (selectedUserId) {
                    conversationsPanel.style.display = 'none';
                    chatPanel.style.display = 'flex';
                } else {
                    conversationsPanel.style.display = 'flex';
                    chatPanel.style.display = 'none';
                }
            } else {
                conversationsPanel.style.display = 'flex';
                chatPanel.style.display = 'flex';
            }
        }

        window.addEventListener('resize', handleMobileView);
        document.addEventListener('DOMContentLoaded', handleMobileView);

        // Add back button for mobile
        if (window.innerWidth <= 768 && selectedUserId) {
            const chatHeader = document.querySelector('.chat-header');
            if (chatHeader) {
                const backButton = document.createElement('button');
                backButton.innerHTML = '<i class="fas fa-arrow-left"></i>';
                backButton.style.cssText = `
                    background: none;
                    border: none;
                    font-size: 1.2rem;
                    color: #667eea;
                    cursor: pointer;
                    padding: 0.5rem;
                    margin-right: 0.5rem;
                `;
                backButton.addEventListener('click', function() {
                    window.location.href = 'chat.php';
                });
                chatHeader.insertBefore(backButton, chatHeader.firstChild);
            }
        }
    </script>
</body>
</html>