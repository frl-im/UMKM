<?php
require_once 'fungsi.php';
// Melindungi halaman untuk semua user yang login
check_login();

global $koneksi;
$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['user_role'];
$selectedUserId = $_GET['user_id'] ?? null;
$selectedUser = null;

// Ambil info user yang dipilih untuk diajak chat
if ($selectedUserId) {
    $stmt = mysqli_prepare($koneksi, "SELECT id, fullname, role, store_name FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $selectedUserId);
    mysqli_stmt_execute($stmt);
    $selectedUser = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Menggunakan fungsi cerdas untuk mengambil daftar percakapan yang relevan
$conversations = ambil_daftar_percakapan($currentUserId, $currentUserRole);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - KreasiLokal.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Baru untuk tampilan yang lebih baik */
        html, body {
            height: 100%;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        .chat-container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 320px;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            background-color: #fff;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .sidebar-header a {
            color: #555;
            font-size: 1.2rem;
            text-decoration: none;
        }
        .conversation-list {
            overflow-y: auto;
            flex-grow: 1;
        }
        .conversation-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }
        .conversation-item:hover {
            background-color: #f9f9f9;
        }
        .conversation-item.active {
            background-color: #e8f5e8;
        }
        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #34A853;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        .conv-details strong {
            display: block;
        }
        .conv-details small {
            color: #777;
        }
        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background-color: #fff;
        }
        .chat-header h3 {
            margin: 0;
        }
        .messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .message-form {
            display: flex;
            padding: 1rem;
            border-top: 1px solid #e0e0e0;
            background-color: #fff;
        }
        .message-form input {
            flex: 1;
            padding: 0.8rem 1rem;
            border-radius: 20px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        .message-form button {
            background-color: #34A853;
            color: white;
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            margin-left: 0.8rem;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .message {
            margin-bottom: 1rem;
            display: flex;
            max-width: 75%;
        }
        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .message .bubble {
            background-color: #f1f1f1;
            padding: 0.8rem 1rem;
            border-radius: 18px;
        }
        .message.sent .bubble {
            background-color: #34A853;
            color: white;
        }
        .empty-chat {
            text-align: center;
            margin: auto;
            color: #999;
        }
        .empty-chat i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Percakapan</h3>
                <a href="index.php" title="Beranda"><i class="fas fa-home"></i></a>
            </div>
            <div class="conversation-list">
                <?php if (empty($conversations)): ?>
                    <p style="padding: 1rem; text-align:center; color: #777;">Tidak ada percakapan.</p>
                <?php else: ?>
                    <?php foreach($conversations as $conv): ?>
                        <a href="chat.php?user_id=<?php echo $conv['id']; ?>" class="conversation-item <?php echo ($selectedUserId == $conv['id']) ? 'active' : ''; ?>">
                            <div class="avatar"><?php echo strtoupper(substr(safe_output($conv['store_name'] ?? $conv['fullname']), 0, 1)); ?></div>
                            <div class="conv-details">
                                <strong><?php echo safe_output($conv['store_name'] ?? $conv['fullname']); ?></strong>
                                <small>(<?php echo safe_output($conv['role']); ?>)</small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-window">
            <?php if ($selectedUser): ?>
                <div class="chat-header">
                    <h3><?php echo safe_output($selectedUser['store_name'] ?? $selectedUser['fullname']); ?></h3>
                </div>
                <div class="messages" id="messages-container">
                    </div>
                <form class="message-form" id="message-form">
                    <input type="text" id="message-input" placeholder="Ketik pesan..." autocomplete="off" required>
                    <button type="submit" title="Kirim Pesan"><i class="fas fa-paper-plane"></i></button>
                </form>
            <?php else: ?>
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <p>Pilih percakapan untuk memulai.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const selectedUserId = <?php echo $selectedUserId ? (int)$selectedUserId : 'null'; ?>;
        let pollingInterval = null;

        if (selectedUserId) {
            loadMessages();
            pollingInterval = setInterval(loadMessages, 3000);
        }
        
        async function loadMessages() {
            if (!selectedUserId) return;

            const response = await fetch(`ajax/ajax_handler.php?action=get_messages&user_id=${selectedUserId}`);
            const data = await response.json();

            if (data.status === 'success') {
                const messagesContainer = document.getElementById('messages-container');
                const currentUserId = <?php echo $currentUserId; ?>;
                
                messagesContainer.innerHTML = '';
                data.data.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message');
                    if (msg.sender_id == currentUserId) {
                        messageDiv.classList.add('sent');
                    }
                    const bubble = document.createElement('div');
                    bubble.classList.add('bubble');
                    bubble.innerText = msg.message;
                    messageDiv.appendChild(bubble);
                    messagesContainer.appendChild(messageDiv);
                });
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        document.getElementById('message-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            if (!message || !selectedUserId) return;

            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('receiver_id', selectedUserId);
            formData.append('message', message);

            const response = await fetch('ajax/ajax_handler.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.status === 'success') {
                messageInput.value = '';
                loadMessages();
            } else {
                alert(data.message || 'Gagal mengirim pesan.');
            }
        });
    </script>
</body>
</html>