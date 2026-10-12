/* assets/js/match.js — Discovery & Swiping logic */

document.addEventListener('DOMContentLoaded', () => {
    const stack = document.getElementById('discoveryStack');
    let cards = [];
    let currentIndex = 0;

    async function loadMatches() {
        try {
            const res = await fetch('api/get_matches.php');
            const data = await res.json();
            if (data.status === 'success') {
                renderStack(data.matches);
            }
        } catch (err) {
            console.error(err);
        }
    }

    function renderStack(matches) {
        if (matches.length === 0) {
            stack.innerHTML = '<div class="search-empty"><h5>No more matches today!</h5><p>Try again later or like more pets to help us learn.</p></div>';
            return;
        }
        
        stack.innerHTML = '';
        cards = matches.map((match, index) => {
            const card = document.createElement('div');
            card.className = 'discovery-card';
            card.style.zIndex = matches.length - index;
            card.innerHTML = `
                <img src="${match.image_path}" alt="">
                <div class="card-content">
                    <span class="match-score">${match.score_pct}% Match</span>
                    <span class="card-name">${match.pet_name}</span>
                    <span class="card-breed">${match.breed || 'Mixed'} • ${match.age || 'Young'}</span>
                    <p style="margin-top: 1rem; color: var(--text-muted); font-size: 0.9rem;">${match.health_status || 'Healthy and happy.'}</p>
                </div>
            `;
            stack.appendChild(card);
            return { el: card, data: match };
        });
        
        currentIndex = 0;
        initDragging();
    }

    window.handleSwipe = async (direction) => {
        if (currentIndex >= cards.length) return;
        
        const card = cards[currentIndex];
        card.el.classList.add(direction === 'right' ? 'swiping-right' : 'swiping-left');
        
        if (direction === 'right') {
            const res = await toggleLike(card.data.id);
            if (res && res.action === 'liked') {
                showMatchOverlay(card.data);
            }
        }
        
        currentIndex++;
        if (currentIndex === cards.length) {
            // Check if overlay is active before rendering empty state
            if (!document.getElementById('matchOverlay').classList.contains('active')) {
                renderStack([]);
            }
        }
    };

    function showMatchOverlay(pet) {
        const overlay = document.getElementById('matchOverlay');
        const petAvatar = document.getElementById('matchedPetAvatar').querySelector('img');
        const startChatBtn = document.getElementById('startChatBtn');
        const adoptNowBtn = document.getElementById('adoptNowBtn');

        petAvatar.src = pet.image_path;
        overlay.classList.add('active');

        // Setup Actions
        startChatBtn.onclick = () => {
            window.location.href = `chat.php?user_id=${pet.user_id}`;
        };

        adoptNowBtn.onclick = () => {
            closeMatchOverlay();
            if (typeof adoptMe === 'function') {
                adoptMe(pet.id);
            }
        };
    }

    window.closeMatchOverlay = () => {
        document.getElementById('matchOverlay').classList.remove('active');
        if (currentIndex === cards.length) {
            renderStack([]);
        }
    };

    function initDragging() {
        // Simple drag logic could be added here for mobile (Touch events)
    }

    loadMatches();
});
