// assets/js/script.js — Feed interactions (Like, Comment, Post Creation)

// ── Like Toggle ───────────────────────────────
async function toggleLike(postId) {
    const card = document.querySelector(`.post-card[data-post-id="${postId}"]`);
    const btn  = card ? card.querySelector('.like-btn') : null;
    const countEl = document.getElementById(`likes-count-${postId}`);

    // Optimistic animation if button exists
    if (btn) {
        btn.classList.add('like-pop');
        setTimeout(() => btn.classList.remove('like-pop'), 400);
    }

    try {
        const res = await fetch('api/like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}`
        });
        const data = await res.json();

        if (data.status === 'success') {
            if (btn) {
                if (data.action === 'liked') {
                    btn.classList.add('liked');
                    const svgWrapper = btn.querySelector('.icon-paw');
                    if (svgWrapper) svgWrapper.classList.add('is-liked');
                } else {
                    btn.classList.remove('liked');
                    const svgWrapper = btn.querySelector('.icon-paw');
                    if (svgWrapper) svgWrapper.classList.remove('is-liked');
                }
            }
            if (countEl) {
                countEl.textContent = data.likes_count;
            }
            return data; // Return data for other callers (like PetMatch)
        }
    } catch (err) {
        console.error('Like error:', err);
    }
    return null;
}

// ── Comment Submit ────────────────────────────
async function submitComment(postId, input) {
    const text = input.value.trim();
    if (!text) return;

    try {
        const res = await fetch('api/comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&comment_text=${encodeURIComponent(text)}`
        });
        const data = await res.json();

        if (data.status === 'success') {
            const container = document.getElementById(`comments-${postId}`);
            const div = document.createElement('div');
            div.className = 'comment comment-new';
            div.innerHTML = `<a href="profile.php?id=${data.user_id}" class="comment-user"><strong>${data.username}</strong></a> ${data.comment_text}`;
            container.appendChild(div);
            input.value = '';
        }
    } catch (err) {
        console.error('Comment error:', err);
    }
}

// ── Focus Comment Input ───────────────────────
function focusComment(postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    if (input) input.focus();
}

// ── Post Creation Modal ───────────────────────
function openPostModal() {
    document.getElementById('postModal').classList.add('active');
    initPostMap();
    setTimeout(() => {
        if (postMap) postMap.invalidateSize();
    }, 100);
}
function closePostModal() {
    document.getElementById('postModal').classList.remove('active');
    document.getElementById('createPostForm').reset();
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (previewContainer) previewContainer.innerHTML = '';
    document.querySelector('.file-upload-content').style.display = 'flex';
}

// Image preview & AI Validation
const postImageInput = document.getElementById('postImage');
let isImageValid = false;
let isModelLoading = false;

if (postImageInput) {
    postImageInput.addEventListener('change', async function () {
        const previewContainer = document.getElementById('imagePreviewContainer');
        previewContainer.innerHTML = '';
        const files = Array.from(this.files);

        if (files.length > 0) {
            document.querySelector('.file-upload-content').style.display = 'none';
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" alt="">`;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });

            // AI Validation on first image for speed/security
            await validatePetImage(files[0]);
        }
    });
}

async function validatePetImage(file) {
    isImageValid = false;
    const reader = new FileReader();
    reader.onload = async (e) => {
        const tempImg = new Image();
        tempImg.src = e.target.result;
        await new Promise(resolve => tempImg.onload = resolve);
        
        try {
            isModelLoading = true;
            Swal.fire({
                title: 'Analyzing Photo...',
                text: 'Ensuring your pet photo is valid.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const model = await mobilenet.load();
            const predictions = await model.classify(tempImg, 5);
            
            const petMap = {
                'dog': 'dog', 'canine': 'dog', 'puppy': 'dog', 'terrier': 'dog', 'retriever': 'dog',
                'cat': 'cat', 'feline': 'cat', 'kitten': 'cat', 'tabby': 'cat', 'siamese': 'cat',
                'bird': 'bird', 'parrot': 'bird', 'macaw': 'bird', 'finch': 'bird', 'dove': 'bird',
                'rabbit': 'rabbit', 'bunny': 'rabbit', 'hare': 'rabbit',
                'hamster': 'hamster', 'guinea pig': 'guinea pig', 'mouse': 'other',
                'fish': 'fish', 'goldfish': 'fish', 'shark': 'fish',
                'turtle': 'turtle', 'tortoise': 'turtle',
                'horse': 'horse', 'pony': 'horse'
            };

            let detectedType = 'other';
            let isValid = false;

            for (let p of predictions) {
                if (p.probability >= 0.3) {
                    const label = p.className.toLowerCase();
                    for (let [key, val] of Object.entries(petMap)) {
                        if (label.includes(key)) {
                            detectedType = val;
                            isValid = true;
                            break;
                        }
                    }
                }
                if (isValid) break;
            }

            if (isValid) {
                isImageValid = true;
                const hiddenType = document.getElementById('hiddenPetType');
                if (hiddenType) hiddenType.value = detectedType;
                
                Swal.fire({ icon: 'success', title: `${detectedType.toUpperCase()} Detected!`, timer: 1000, showConfirmButton: false });
            } else {
                isImageValid = false;
                postImageInput.value = '';
                document.getElementById('imagePreviewContainer').innerHTML = '';
                document.querySelector('.file-upload-content').style.display = 'flex';
                Swal.fire({ icon: 'error', title: 'Validation Failed', text: 'Please upload a clear photo of a pet.' });
            }
        } catch (err) {
            console.error('TF Error:', err);
        } finally {
            isModelLoading = false;
        }
    };
    reader.readAsDataURL(file);
}

// ── Multi-Image Carousel Logic ────────────────
function moveCarousel(btn, direction) {
    const container = btn.closest('.post-carousel');
    const images = container.querySelectorAll('.post-img');
    const dots = container.querySelectorAll('.dot');
    let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));

    images[currentIndex].classList.remove('active');
    dots[currentIndex].classList.remove('active');

    currentIndex += direction;
    if (currentIndex >= images.length) currentIndex = 0;
    if (currentIndex < 0) currentIndex = images.length - 1;

    images[currentIndex].classList.add('active');
    dots[currentIndex].classList.add('active');
}


// Form submission
const createPostForm = document.getElementById('createPostForm');
if (createPostForm) {
    createPostForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (isModelLoading) {
            Swal.fire({ icon: 'info', title: 'Please Wait', text: 'Image analysis is still in progress.' });
            return;
        }

        if (!postImageInput.value || !isImageValid) {
            Swal.fire({ icon: 'warning', title: 'No Valid Image', text: 'Please upload a valid pet photo.' });
            return;
        }

        const formData = new FormData(createPostForm);
        const submitBtn = createPostForm.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';

        try {
            const res = await fetch('api/create_post.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === 'success') {
                closePostModal();
                location.reload();
            } else {
                Swal.fire('Error', data.message || 'Error creating post', 'error');
            }
        } catch (err) {
            console.error('Post creation error:', err);
            Swal.fire('Error', 'An error occurred. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Create Post';
        }
    });
}

// ── Post Creation Map & Geocoding ─────────────
let postMap = null;
let postMarker = null;

function initPostMap() {
    if (document.getElementById('createPostMap') && !postMap) {
        // Default to a central location or let user search
        postMap = L.map('createPostMap').setView([36.7538, 3.0588], 5);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(postMap);

        postMarker = L.marker([36.7538, 3.0588], { draggable: true }).addTo(postMap);

        // Update hidden inputs when marker is dragged
        postMarker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            document.getElementById('latInput').value = pos.lat;
            document.getElementById('lngInput').value = pos.lng;
        });

        // Initialize hidden inputs to default marker pos
        document.getElementById('latInput').value = 36.7538;
        document.getElementById('lngInput').value = 3.0588;

        // Map Click to move marker
        postMap.on('click', function (e) {
            postMarker.setLatLng(e.latlng);
            document.getElementById('latInput').value = e.latlng.lat;
            document.getElementById('lngInput').value = e.latlng.lng;
        });

        // Geocoding Search
        const searchInput = document.getElementById('mapSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', async function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value.trim();
                    if (!query) return;

                    searchInput.disabled = true;
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
                        const data = await res.json();
                        
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lon = parseFloat(data[0].lon);
                            
                            postMap.flyTo([lat, lon], 13);
                            postMarker.setLatLng([lat, lon]);
                            
                            document.getElementById('latInput').value = lat;
                            document.getElementById('lngInput').value = lon;
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Location not found',
                                text: 'We could not find that place. Try a different search term.',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        }
                    } catch (err) {
                        console.error('Geocoding error:', err);
                    } finally {
                        searchInput.disabled = false;
                        searchInput.focus();
                    }
                }
            });
        }
    }
}



// ── Adoption Action ──────────────────────────────
async function adoptMe(postId) {
    const { value: message } = await Swal.fire({
        title: window.translations.adopt_title || 'Adopt This Pet',
        input: 'textarea',
        inputLabel: window.translations.adopt_question || 'Why do you want to adopt this pet?',
        inputPlaceholder: window.translations.adopt_placeholder || 'Type your message here...',
        inputAttributes: {
            'aria-label': 'Type your message here'
        },
        showCancelButton: true,
        confirmButtonText: window.translations.send_request || 'Send Request',
        confirmButtonColor: 'var(--primary)'
    });

    if (message) {
        try {
            const res = await fetch('api/adopt_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&message=${encodeURIComponent(message)}`
            });
            const data = await res.json();
            if (data.status === 'success') {
                Swal.fire('Sent!', 'Your adoption request has been sent to the owner.', 'success');
            } else {
                Swal.fire('Error', data.message || 'Could not send request', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Server Error', 'error');
        }
    }
}

// ── Delete Post ──────────────────────────────
async function deletePost(postId) {
    if (!confirm(window.translations.confirm_delete || 'Are you sure you want to delete this post?')) return;
    
    try {
        const res = await fetch('api/delete_post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}`
        });
        const data = await res.json();
        if (data.status === 'success') {
            const el = document.getElementById(`post-${postId}`);
            if (el) el.remove();
            else location.reload();
        } else {
            alert(data.message || 'Error deleting post');
        }
    } catch (err) {
        alert('Server Error');
    }
}

// ── Pet SVG Animations ────────────────────────
function animateBird(btn) {
    const bird = btn.querySelector('.icon-bird');
    if (bird) {
        bird.classList.add('is-flying');
        setTimeout(() => bird.classList.remove('is-flying'), 600);
    }
}
