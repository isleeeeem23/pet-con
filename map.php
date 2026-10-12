<?php
// map.php — Discover Page with Interactive Map
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="discover-page">
    <div class="page-header">
        <div class="page-header-text">
            <h2><i class="fa-solid fa-map-location-dot"></i> <?php echo __('discover'); ?></h2>
            <p class="page-subtitle"><?php echo __('available_pets'); ?></p>
        </div>
    </div>

    <div class="map-wrapper">
        <div id="map"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize map
    const map = L.map('map', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([33.5731, -7.5898], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Custom paw icon
    const pawIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div class="marker-pin"><i class="fa-solid fa-paw"></i></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -42]
    });

    async function loadMarkers() {
        try {
            const response = await fetch('api/get_locations.php');
            const locations = await response.json();

            const bounds = [];

            locations.forEach(loc => {
                const marker = L.marker([loc.lat, loc.lng], { icon: pawIcon }).addTo(map);
                bounds.push([loc.lat, loc.lng]);

                marker.bindPopup(`
                    <div class="map-popup">
                        <img src="${loc.image_path}" alt="${loc.pet_name}">
                        <div class="map-popup-info">
                            <strong>${loc.pet_name}</strong>
                            <span>${loc.breed || ''}</span>
                            <a href="index.php#post-${loc.id}" class="popup-link">
                                <i class="fa-solid fa-arrow-right"></i> <?php echo __('view_post'); ?>
                            </a>
                        </div>
                    </div>
                `);
            });

            // Fit map to markers
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 10 });
            }
        } catch (error) {
            console.error('Error loading locations:', error);
        }
    }

    loadMarkers();
</script>

<?php include 'includes/footer.php'; ?>
