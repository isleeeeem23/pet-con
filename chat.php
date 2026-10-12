<?php
// chat.php — Real-Time Messaging with AJAX Polling
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get list of contacts (people user has chatted with)
$sql = "SELECT DISTINCT u.id, u.username, u.avatar,
        (SELECT message_content FROM messages 
         WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
         ORDER BY sent_at DESC LIMIT 1) AS last_message,
        (SELECT sent_at FROM messages 
         WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
         ORDER BY sent_at DESC LIMIT 1) AS last_time,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) AS unread_count
        FROM users u 
        JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
        ORDER BY last_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$contacts = $stmt->fetchAll();

// If coming from a post "Contact Owner" link
$preselect_user = isset($_GET['to']) ? (int)$_GET['to'] : null;
if ($preselect_user && $preselect_user !== $user_id) {
    // Check if this user is already in contacts
    $found = false;
    foreach ($contacts as $c) {
        if ($c['id'] == $preselect_user) {
            $found = true;
            break;
        }
    }
    // If not found, fetch the user and add to the top
    if (!$found) {
        $stmt = $pdo->prepare("SELECT id, username, avatar FROM users WHERE id = ?");
        $stmt->execute([$preselect_user]);
        $newContact = $stmt->fetch();
        if ($newContact) {
            $newContact['last_message'] = null;
            $newContact['last_time'] = null;
            $newContact['unread_count'] = 0;
            array_unshift($contacts, $newContact);
        }
    }
}

include 'includes/header.php';
?>

<div class="chat-page">
    <div class="chat-container">
        <!-- Contacts Sidebar -->
        <div class="chat-sidebar" id="chatSidebar">
            <div class="sidebar-header">
                <h3><i class="fa-solid fa-comment-dots"></i> <?php echo __('all_conversations'); ?></h3>
            </div>
            <div class="sidebar-search">
                <div class="input-wrapper">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchUsers" placeholder="<?php echo __('search_users'); ?>"
                           onkeyup="searchUsers(this.value)">
                </div>
                <div id="searchResults" class="search-results"></div>
            </div>
            <div class="contacts-list" id="contactsList">
                <?php if (empty($contacts)): ?>
                    <div class="empty-contacts">
                        <p><?php echo __('no_messages'); ?></p>
                    </div>
                <?php endif; ?>
                <?php foreach ($contacts as $contact): ?>
                    <div class="contact-item <?php echo ($preselect_user == $contact['id']) ? 'active' : ''; ?>" 
                         data-user-id="<?php echo $contact['id']; ?>"
                         onclick="loadChat(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars($contact['username'], ENT_QUOTES); ?>', this)">
                        <div class="avatar avatar-sm">
                            <?php if ($contact['avatar'] && $contact['avatar'] !== 'default_user.png'): ?>
                                <img src="uploads/avatars/<?php echo htmlspecialchars($contact['avatar']); ?>" alt="">
                            <?php else: ?>
                                <i class="fa-solid fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="contact-info">
                            <strong><?php echo htmlspecialchars($contact['username']); ?></strong>
                            <?php if ($contact['last_message']): ?>
                                <span class="last-msg"><?php echo mb_strimwidth(htmlspecialchars($contact['last_message']), 0, 30, '...'); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($contact['unread_count'])): ?>
                            <span class="unread-badge"><?php echo $contact['unread_count']; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Chat Window -->
        <div class="chat-window" id="chatWindow">
            <div class="chat-header" id="chatHeader">
                <button class="btn-icon chat-back-btn" onclick="showSidebar()">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <span id="chatHeaderName"><?php echo __('select_contact'); ?></span>
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="chat-placeholder">
                    <i class="fa-solid fa-comment-dots"></i>
                    <p><?php echo __('select_contact'); ?></p>
                </div>
            </div>
            <div class="chat-footer" id="chatFooter" style="display: none;">
                <input type="text" id="messageInput" placeholder="<?php echo __('type_message'); ?>"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="btn btn-primary btn-icon" onclick="sendMessage()">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
    const preselectUser = <?php echo $preselect_user ? $preselect_user : 'null'; ?>;
</script>
<script src="assets/js/chat.js"></script>

<?php include 'includes/footer.php'; ?>
