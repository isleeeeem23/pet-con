<?php
// pet_match.php — AI Pet Discovery Mode
require_once 'config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
?>

<div class="match-container">
    <div class="match-header">
        <div class="match-title">
            <?php echo petIcon('sparkles', 'match-sparkles'); ?>
            <h2>PetMatch Discovery</h2>
        </div>
        <p>Swiping for your next best friend. AI-powered recommendations based on your interests.</p>
    </div>

    <div class="discovery-stack" id="discoveryStack">
        <!-- Cards loaded via JS -->
        <div class="discovery-loader">
            <div class="bone-spinner">
                <?php echo petIcon('bone', 'spinning-bone'); ?>
            </div>
            <p>Scanning the shelter for you...</p>
        </div>
    </div>

    <div class="match-controls">
        <button class="match-btn btn-no" onclick="handleSwipe('left')">
             <i class="fa-solid fa-xmark"></i>
        </button>
        <button class="match-btn btn-yes" onclick="handleSwipe('right')">
             <?php echo petIcon('paw', 'btn-icon'); ?>
        </button>
    </div>

    <!-- Match Overlay -->
    <div id="matchOverlay" class="match-overlay">
        <div class="overlay-content">
            <div class="match-confetti" id="confetti"></div>
            <h1 class="match-heading"><?php echo $translations['its_a_match']; ?></h1>
            <p class="match-sub"><?php echo $translations['match_subtitle']; ?></p>
            
            <div class="match-visual">
                <div class="user-avatar-match">
                    <img src="uploads/avatars/<?php echo $_SESSION['user_avatar'] ?? 'default_user.png'; ?>" alt="">
                </div>
                <div class="heart-match">
                    <?php echo petIcon('heart', 'heart-pulse'); ?>
                </div>
                <div class="pet-avatar-match" id="matchedPetAvatar">
                    <img src="" alt="">
                </div>
            </div>

            <div class="overlay-actions">
                <button class="match-action-btn btn-primary" id="startChatBtn">
                    <i class="fa-solid fa-comment"></i> <?php echo $translations['start_chat']; ?>
                </button>
                <button class="match-action-btn btn-secondary" id="adoptNowBtn">
                    <i class="fa-solid fa-heart"></i> <?php echo $translations['adopt_now']; ?>
                </button>
                <button class="match-action-btn btn-link" onclick="closeMatchOverlay()">
                    <?php echo $translations['keep_swiping']; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.match-container {
    max-width: 500px;
    margin: 2rem auto;
    padding: 0 1rem;
    text-align: center;
}

.match-header { margin-bottom: 2rem; }
.match-title { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 0.5rem; }
.match-title h2 { margin: 0; font-size: 1.8rem; color: var(--primary); }
.match-sparkles { width: 30px; height: 30px; color: var(--accent); }

.discovery-stack {
    position: relative;
    height: 60vh;
    perspective: 1000px;
}

.discovery-card {
    position: absolute;
    width: 100%;
    height: 100%;
    background: var(--bg-card);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
    cursor: grab;
    transition: transform 0.5s ease, opacity 0.5s ease;
    display: flex;
    flex-direction: column;
}

.discovery-card img {
    height: 70%;
    width: 100%;
    object-fit: cover;
}

.card-content {
    padding: 1.5rem;
    text-align: left;
    flex: 1;
    position: relative;
    background: linear-gradient(to bottom, transparent, var(--bg-card) 20px);
    margin-top: -20px;
}

.card-name { font-size: 1.5rem; font-weight: 700; display: block; }
.card-breed { color: var(--text-muted); font-size: 1rem; }
.match-score {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    background: var(--primary);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.match-controls {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 2rem;
}

.match-btn {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: transform 0.2s;
}

.match-btn:active { transform: scale(0.9); }
.btn-no { background: white; color: #ff4444; border: 2px solid #ff4444; }
.btn-yes { background: var(--primary); color: white; }
.btn-yes .btn-icon { width: 30px; height: 30px; }

/* Swiping Classes */
.swiping-left { transform: translateX(-150%) rotate(-30deg) !important; opacity: 0; }
.swiping-right { transform: translateX(150%) rotate(30deg) !important; opacity: 0; }

.spinning-bone { width: 40px; height: 40px; color: var(--primary); animation: boneSpin 2s linear infinite; }
@keyframes boneSpin { to { transform: rotate(360deg); } }

/* Match Overlay */
.match-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
}

.match-overlay.active {
    display: flex;
    animation: fadeIn 0.4s ease;
}

.overlay-content {
    text-align: center;
    color: white;
    max-width: 400px;
    padding: 2rem;
}

.match-heading {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #ff6b6b, #feca57);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: scaleIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.match-visual {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin: 3rem 0;
}

.user-avatar-match, .pet-avatar-match {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid white;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(255,255,255,0.3);
}

.user-avatar-match img, .pet-avatar-match img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.heart-match {
    font-size: 2.5rem;
    color: #ff4757;
    animation: heartPulse 1.5s infinite;
}

.overlay-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.match-action-btn {
    padding: 15px;
    border-radius: 30px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, background 0.2s;
}

.match-action-btn:hover { transform: translateY(-2px); }
.match-action-btn.btn-primary { background: var(--primary); color: white; }
.match-action-btn.btn-secondary { background: white; color: var(--primary); }
.match-action-btn.btn-link { background: transparent; color: #ccc; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes scaleIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
@keyframes heartPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.3); }
}

.heart-pulse { width: 50px; height: 50px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/match.js?v=<?php echo time(); ?>"></script>
<?php include 'includes/footer.php'; ?>
