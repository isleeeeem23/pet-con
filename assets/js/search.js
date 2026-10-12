/* assets/js/search.js — Live Search & Following Logic */

document.addEventListener('DOMContentLoaded', () => {
    const searchInputs = document.querySelectorAll('.nav-search-input');

    searchInputs.forEach(input => {
        const dropdownId = input.getAttribute('data-dropdown');
        const dropdown = document.getElementById(dropdownId);
        let debounceTimer;

        if (!dropdown) return;

        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                dropdown.classList.remove('active');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetchSearchResults(query, dropdown);
            }, 300);
        });
    });

    // Close all dropdowns on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-input-wrapper')) {
            document.querySelectorAll('.search-results-dropdown').forEach(d => d.classList.remove('active'));
        }
    });

    async function fetchSearchResults(query, dropdown) {
        try {
            const res = await fetch(`api/search_users.php?q=${encodeURIComponent(query)}`);
            const data = await res.json();

            if (data.status === 'success') {
                renderSearchResults(data.users, dropdown);
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    function renderSearchResults(users, dropdown) {
        if (users.length === 0) {
            dropdown.innerHTML = `
                <div class="search-empty">
                    <div class="empty-illustration">
                        <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 20 L12 8 L24 16 Z" fill="var(--primary)"/>
                            <path d="M46 20 L52 8 L40 16 Z" fill="var(--primary)"/>
                            <circle cx="32" cy="30" r="18" fill="var(--bg-card)" stroke="var(--primary)" stroke-width="2"/>
                            <circle cx="25" cy="28" r="2" fill="var(--primary)"/>
                            <circle cx="39" cy="28" r="2" fill="var(--primary)"/>
                            <rect x="36" y="32" width="22" height="6" rx="2" fill="var(--text-main)" transform="rotate(-15 36 32)"/>
                            <rect x="35" y="31" width="5" height="8" rx="1" fill="var(--primary)"/>
                            <line x1="10" y1="56" x2="54" y2="56" stroke="var(--text-muted)" stroke-width="1" stroke-dasharray="4 2"/>
                        </svg>
                    </div>
                    <h5>No humans found!</h5>
                    <p>Try searching for a different name or location.</p>
                </div>
            `;
        } else {
            dropdown.innerHTML = users.map(user => `
                <div class="search-result-item">
                    <a href="profile.php?id=${user.id}" class="search-result-link" style="display:flex; align-items:center; gap:12px; flex:1; text-decoration:none; color:inherit;">
                        <img src="uploads/avatars/${user.avatar || 'default_user.png'}" class="result-avatar" alt="">
                        <div class="result-info">
                            <div class="result-name">
                                ${user.username}
                                ${user.role === 'shelter' ? '<span class="badge-shelter">Shelter</span>' : ''}
                            </div>
                            <div class="result-meta">
                                ${user.location ? user.location : 'Planet Earth'} • ${user.post_count} posts
                            </div>
                        </div>
                    </a>
                    <button class="btn ${user.is_following > 0 ? 'btn-outline' : 'btn-primary'} btn-follow" 
                            data-user-id="${user.id}" 
                            onclick="toggleFollow(this, event)">
                        ${user.is_following > 0 ? 'Following' : 'Follow'}
                    </button>
                </div>
            `).join('');
        }
        dropdown.classList.add('active');
    }
});

async function toggleFollow(btn, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const userId = btn.getAttribute('data-user-id');
    const originalText = btn.textContent.trim();
    
    btn.disabled = true;
    
    try {
        const res = await fetch('api/toggle_follow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}`
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            if (data.action === 'followed') {
                btn.textContent = 'Following';
                btn.classList.replace('btn-primary', 'btn-outline');
            } else {
                btn.textContent = 'Follow';
                btn.classList.replace('btn-outline', 'btn-primary');
            }
        }
    } catch (err) {
        console.error('Follow error:', err);
    } finally {
        btn.disabled = false;
    }
}
