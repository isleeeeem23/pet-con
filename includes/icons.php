<?php
/**
 * includes/icons.php — Pet-themed SVG icon library
 * Usage: echo petIcon('paw') echos the SVG with correct classes.
 *
 * Available icons: paw, bubble, bird, bone, catbell, fish, shield
 */

function petIcon(string $name, string $extra = ''): string {
    $icons = [

        /* ── PAW PRINT ────────────────────────────────────────── */
        'paw' => '
<span class="pet-icon icon-paw ' . $extra . '" aria-label="Like">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <!-- Main pad -->
    <ellipse class="paw-pad" cx="16" cy="20" rx="7.5" ry="6"/>
    <!-- Toes -->
    <ellipse class="paw-toe" cx="9"  cy="13" rx="2.5" ry="3" transform="rotate(-20,9,13)"/>
    <ellipse class="paw-toe" cx="14" cy="11" rx="2.5" ry="3" transform="rotate(-5,14,11)"/>
    <ellipse class="paw-toe" cx="19" cy="11" rx="2.5" ry="3" transform="rotate(5,19,11)"/>
    <ellipse class="paw-toe" cx="24" cy="13" rx="2.5" ry="3" transform="rotate(20,24,13)"/>
  </svg>
</span>',

        /* ── WHISKERED SPEECH BUBBLE ──────────────────────────── */
        'bubble' => '
<span class="pet-icon icon-bubble ' . $extra . '" aria-label="Comment">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <!-- Bubble body -->
    <path class="bubble-body" d="M4 6 Q4 3 7 3 H25 Q28 3 28 6 V18 Q28 21 25 21 H19 L16 26 L13 21 H7 Q4 21 4 18 Z"/>
    <!-- 3 dots -->
    <circle class="bubble-dot" cx="11.5" cy="12" r="1.6"/>
    <circle class="bubble-dot" cx="16"   cy="12" r="1.6"/>
    <circle class="bubble-dot" cx="20.5" cy="12" r="1.6"/>
    <!-- Whiskers -->
    <line class="bubble-whisker" x1="6"  y1="21" x2="2"  y2="24"/>
    <line class="bubble-whisker" x1="8"  y1="23" x2="4"  y2="27"/>
    <line class="bubble-whisker" x1="26" y1="21" x2="30" y2="24"/>
    <line class="bubble-whisker" x1="24" y1="23" x2="28" y2="27"/>
  </svg>
</span>',

        /* ── BIRD IN FLIGHT ───────────────────────────────────── */
        'bird' => '
<span class="pet-icon icon-bird ' . $extra . '" aria-label="Contact owner">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <!-- Body -->
    <ellipse class="bird-body" cx="16" cy="19" rx="5" ry="3.5"/>
    <!-- Head -->
    <circle  class="bird-body" cx="22" cy="16" r="3"/>
    <!-- Beak -->
    <polygon class="bird-body" points="25,15 29,16.5 25,17.5"/>
    <!-- Eye -->
    <circle fill="var(--bg-card)" cx="23" cy="15.5" r="0.9"/>
    <!-- Wing top -->
    <path class="bird-wing" d="M14 17 Q8 10 4 11 Q9 14 11 17 Z"/>
    <!-- Tail -->
    <path class="bird-body" d="M11 20 Q7 21 5 24 Q10 22 12 20 Z"/>
  </svg>
</span>',

        /* ── BONE ─────────────────────────────────────────────── */
        'bone' => '
<span class="pet-icon icon-bone ' . $extra . '" aria-label="Messages">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <!-- Shaft -->
    <rect class="bone-body" x="10" y="13.5" width="12" height="5" rx="2.5"/>
    <!-- Left knobs -->
    <circle class="bone-body" cx="9"  cy="12" r="3.3"/>
    <circle class="bone-body" cx="9"  cy="20" r="3.3"/>
    <!-- Right knobs -->
    <circle class="bone-body" cx="23" cy="12" r="3.3"/>
    <circle class="bone-body" cx="23" cy="20" r="3.3"/>
  </svg>
</span>',



        /* ── FISH ─────────────────────────────────────────────── */
        'fish' => '
<span class="pet-icon icon-fish ' . $extra . '" aria-label="Messages">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <!-- Body -->
    <ellipse class="fish-body" cx="15" cy="16" rx="8" ry="5.5"/>
    <!-- Tail -->
    <path class="fish-fin" d="M7 16 Q2 11 2 16 Q2 21 7 16 Z"/>
    <!-- Eye -->
    <circle fill="var(--bg-card)" cx="20.5" cy="14.5" r="1.4"/>
    <circle fill="var(--icon-color)" cx="20.5" cy="14.5" r="0.7"/>
    <!-- Fin -->
    <path class="fish-fin" d="M14 11 Q16 7 18 11 Z"/>
  </svg>
</span>',

        /* ── PAW SHIELD (Profile) ─────────────────────────────── */
        'shield' => '
<span class="pet-icon icon-paw ' . $extra . '" aria-label="Profile">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <path class="paw-pad" d="M16 3 L27 8 V17 Q27 26 16 30 Q5 26 5 17 V8 Z"/>
    <ellipse fill="var(--bg-card)" cx="16" cy="20" rx="5.5" ry="4.5" opacity="0.3"/>
    <ellipse fill="var(--bg-card)" cx="10.5" cy="15" rx="2" ry="2.5" transform="rotate(-20,10.5,15)" opacity="0.35"/>
    <ellipse fill="var(--bg-card)" cx="14.5" cy="13" rx="2" ry="2.5" opacity="0.35"/>
    <ellipse fill="var(--bg-card)" cx="18.5" cy="13" rx="2" ry="2.5" opacity="0.35"/>
    <ellipse fill="var(--bg-card)" cx="22" cy="15" rx="2" ry="2.5" transform="rotate(20,22,15)" opacity="0.35"/>
  </svg>
</span>',

        /* ── MAGNIFIER ────────────────────────────────────────── */
        'magnifier' => '
<span class="pet-icon icon-magnifier ' . $extra . '" aria-label="Search">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <circle class="magnifier-glass" cx="13" cy="13" r="10" stroke-width="2.5" fill="none" stroke="currentColor"/>
    <line class="magnifier-handle" x1="20" y1="20" x2="29" y2="29" stroke-width="3" stroke-linecap="round" stroke="currentColor"/>
    <circle cx="13" cy="13" r="2.5" fill="currentColor"/> <!-- Paw center -->
  </svg>
</span>',

        /* ── CAT LOOKING THROUGH TELESCOPE ────────────────────── */
        'cat-telescope' => '
<span class="pet-icon icon-cat-telescope ' . $extra . '" aria-label="No humans found">
  <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
    <!-- Cat Ears -->
    <path d="M18 20 L12 8 L24 16 Z" fill="var(--primary)"/>
    <path d="M46 20 L52 8 L40 16 Z" fill="var(--primary)"/>
    <!-- Cat Head -->
    <circle cx="32" cy="30" r="18" fill="var(--bg-card)" stroke="var(--primary)" stroke-width="2"/>
    <!-- Eyes -->
    <circle cx="25" cy="28" r="2" fill="var(--primary)"/>
    <circle cx="39" cy="28" r="2" fill="var(--primary)"/>
    <!-- Telescope -->
    <rect x="36" y="32" width="22" height="6" rx="2" fill="var(--text-main)" transform="rotate(-15 36 32)"/>
    <rect x="35" y="31" width="5" height="8" rx="1" fill="var(--primary)"/>
    <!-- Ground -->
    <line x1="10" y1="56" x2="54" y2="56" stroke="var(--text-muted)" stroke-width="1" stroke-dasharray="4 2"/>
  </svg>
</span>',

        /* ── GALLERY ─────────────────────────────────────────── */
        'gallery' => '
<span class="pet-icon icon-gallery ' . $extra . '" aria-label="Gallery">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <rect x="4" y="8" width="20" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
    <rect x="8" y="4" width="20" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2" opacity="0.6"/>
    <circle cx="10" cy="14" r="2" fill="currentColor"/>
    <path d="M4 22 L10 16 L14 20 L20 14 L24 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
  </svg>
</span>',

        /* ── SPARKLES (AI Match) ─────────────────────────────── */
        'sparkles' => '
<span class="pet-icon icon-sparkles ' . $extra . '" aria-label="AI match">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 4 L18 10 L24 12 L18 14 L16 20 L14 14 L8 12 L14 10 Z" fill="currentColor">
       <animate attributeName="opacity" values="1;0.5;1" dur="2s" repeatCount="indefinite" />
    </path>
    <path d="M26 18 L27 21 L30 22 L27 23 L26 26 L25 23 L22 22 L25 21 Z" fill="currentColor" opacity="0.7"/>
    <path d="M6 20 L7 23 L10 24 L7 25 L6 28 L5 25 L2 24 L5 23 Z" fill="currentColor" opacity="0.7"/>
  </svg>
</span>',

        /* ── HEART (Match) ───────────────────────────────────── */
        'heart' => '
<span class="pet-icon icon-heart ' . $extra . '" aria-label="Match">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 28 L14.5 26.5 C9.5 22 6 18.5 6 14.5 C6 11.5 8.5 9 11.5 9 C13.5 9 15 10 16 11.5 C17 10 18.5 9 20.5 9 C23.5 9 26 11.5 26 14.5 C26 18.5 22.5 22 17.5 26.5 Z" fill="#ff4757">
    </path>
  </svg>
</span>',

    ];

    return $icons[$name] ?? '';
}
