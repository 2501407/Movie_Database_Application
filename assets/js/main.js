// Edit Mode 
let editModeActive = false;

function toggleEditMode() {
    editModeActive = !editModeActive;
    
    const banner = document.getElementById('editModeBanner');
    const editBtn = document.getElementById('editModeBtn');
    const editableElements = document.querySelectorAll('.edit-mode-only');
    
    if (editModeActive) {
        // Show edit mode
        if (banner) banner.style.display = 'flex';
        if (editBtn) {
            editBtn.classList.add('active');
            editBtn.textContent = '✓ Edit Mode';
        }
        editableElements.forEach(el => el.style.display = 'inline-flex');
    } else {
        // Hide edit mode
        if (banner) banner.style.display = 'none';
        if (editBtn) {
            editBtn.classList.remove('active');
            editBtn.textContent = '✏️ Edit Mode';
        }
        editableElements.forEach(el => el.style.display = 'none');
    }
}

document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-enable Edit Mode on first login 
    const welcomeBanner = document.querySelector('.welcome-banner');
    if (welcomeBanner) {
        // If loggged in - auto-enable edit mode
        toggleEditMode();
    }

    // ── Ajax Autocomplete Search 
    const searchInput = document.getElementById('searchInput');
    const movieTable  = document.getElementById('movieTable');

    if (searchInput && movieTable) {
        let debounceTimer = null;

        // Create autocomplete dropdown container
        const dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown';
        dropdown.style.display = 'none';
        searchInput.parentElement.appendChild(dropdown);

        console.log('Autocomplete initialized');

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            console.log('Search input:', query);

            if (query.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(function () {
                const url = 'index.php?action=autocomplete&q=' + encodeURIComponent(query);
                console.log('Fetching:', url);
                
                // Fetch suggestions from index.php with action=autocomplete
                fetch(url)
                    .then(r => {
                        console.log('Response received:', r.status);
                        return r.json();
                    })
                    .then(suggestions => {
                        console.log('Suggestions:', suggestions);
                        dropdown.innerHTML = '';

                        if (!suggestions || suggestions.length === 0) {
                            const noResults = document.createElement('div');
                            noResults.className = 'autocomplete-item autocomplete-no-results';
                            noResults.textContent = 'No movies found';
                            dropdown.appendChild(noResults);
                            dropdown.style.display = 'block';
                            return;
                        }

                        suggestions.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            
                            // Create formatted suggestion with title, year, genre, director
                            div.innerHTML = `
                                <div class="suggestion-title">${escapeHtml(item.title)} <span class="suggestion-year">(${item.year})</span></div>
                                <div class="suggestion-meta">${escapeHtml(item.genre)} · ${escapeHtml(item.director)}</div>
                            `;
                            
                            div.onclick = function () {
                                console.log('Redirecting to view.php?id=' + item.id);
                                window.location.href = 'view.php?id=' + item.id;
                            };
                            dropdown.appendChild(div);
                        });

                        dropdown.style.display = 'block';
                        console.log('Dropdown displayed');
                    })
                    .catch((error) => {
                        console.error('Autocomplete fetch error:', error);
                    });
            }, 300);   // 300ms debounce
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        
        // Also close on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ── Page Slider 
    const pageSlider = document.getElementById('pageSlider');
    
    if (pageSlider) {
        pageSlider.addEventListener('input', function() {
            const page = this.value;
            const fill = document.querySelector('.slider-fill');
            const max = parseInt(this.max);
            const percentage = ((page - 1) / Math.max(1, max - 1)) * 100;
            
            if (fill) {
                fill.style.width = percentage + '%';
            }
        });
        
        pageSlider.addEventListener('change', function() {
            const page = this.value;
            window.location.href = '?page=' + page;
        });
    }
});