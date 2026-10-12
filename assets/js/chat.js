// assets/js/chat.js — Real-time messaging with 5s AJAX polling

let currentReceiverId = null;
let pollingInterval = null;

// ── Load Chat ─────────────────────────────────
function loadChat(receiverId, username, el) {
    currentReceiverId = receiverId;
    
    // Update header
    document.getElementById('chatHeaderName').textContent = username;
    document.getElementById('chatFooter').style.display = 'flex';
    document.getElementById('chatMessages').innerHTML = '';

    // Remove placeholder
    const placeholder = document.querySelector('.chat-placeholder');
    if (placeholder) placeholder.remove();

    // Active state
    document.querySelectorAll('.contact-item').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');

    // Remove unread badge
    if (el) {
        const badge = el.querySelector('.unread-badge');
        if (badge) badge.remove();
    }

    // Mobile: show chat window, hide sidebar
    document.getElementById('chatSidebar').classList.add('hidden-mobile');
    document.getElementById('chatWindow').classList.add('active-mobile');

    // Fetch messages
    fetchMessages(true);

    // Start polling every 5 seconds
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(() => fetchMessages(false), 5000);
}

// ── Show Sidebar (mobile back button) ─────────
function showSidebar() {
    document.getElementById('chatSidebar').classList.remove('hidden-mobile');
    document.getElementById('chatWindow').classList.remove('active-mobile');
}

// ── Fetch Messages ────────────────────────────
async function fetchMessages(scrollDown = false) {
    if (!currentReceiverId) return;

    try {
        const res = await fetch(`api/messages.php?receiver_id=${currentReceiverId}`);
        const messages = await res.json();

        const container = document.getElementById('chatMessages');
        let html = '';

        messages.forEach(msg => {
            const type = msg.sender_id == currentUserId ? 'sent' : 'received';
            const time = new Date(msg.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            html += `<div class="message ${type}">
                        <div class="message-text">${escapeHtml(msg.message_content)}</div>
                        <div class="message-time">${time}</div>
                     </div>`;
        });

        container.innerHTML = html;
        if (scrollDown || isNearBottom(container)) {
            container.scrollTop = container.scrollHeight;
        }
    } catch (err) {
        console.error('Fetch messages error:', err);
    }
}

// ── Send Message ──────────────────────────────
async function sendMessage() {
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text || !currentReceiverId) return;

    input.value = '';

    try {
        const res = await fetch('api/messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `receiver_id=${currentReceiverId}&message_content=${encodeURIComponent(text)}`
        });
        const data = await res.json();

        if (data.status === 'success') {
            fetchMessages(true);
        }
    } catch (err) {
        console.error('Send message error:', err);
    }
}

// ── Search Users ──────────────────────────────
let searchTimeout = null;
async function searchUsers(query) {
    const results = document.getElementById('searchResults');
    
    if (query.length < 2) {
        results.innerHTML = '';
        results.style.display = 'none';
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(`api/search_users.php?q=${encodeURIComponent(query)}`);
            const users = await res.json();

            if (users.length === 0) {
                results.innerHTML = '<div class="search-empty">No users found</div>';
            } else {
                results.innerHTML = users.map(u => `
                    <div class="search-result-item" onclick="startChatWith(${u.id}, '${escapeHtml(u.username)}')">
                        <div class="avatar avatar-xs">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span>${escapeHtml(u.username)}</span>
                    </div>
                `).join('');
            }
            results.style.display = 'block';
        } catch (err) {
            console.error('Search error:', err);
        }
    }, 300);
}

function startChatWith(userId, username) {
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('searchUsers').value = '';
    loadChat(userId, username, null);
}

// ── Helpers ───────────────────────────────────
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function isNearBottom(el) {
    return el.scrollHeight - el.scrollTop - el.clientHeight < 100;
}

// ── Auto-load preselected user ────────────────
if (typeof preselectUser !== 'undefined' && preselectUser) {
    const contact = document.querySelector(`.contact-item[data-user-id="${preselectUser}"]`);
    if (contact) {
        const username = contact.querySelector('strong').textContent;
        loadChat(preselectUser, username, contact);
    }
}
