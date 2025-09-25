document.addEventListener('DOMContentLoaded', () => {
    // --- Utility Functions ---
    // Simple notification display - can be enhanced with style.css for better visuals
    function showAlert(message, type = 'info') {
        alert(`${type.toUpperCase()}: ${message}`);
    }

    // --- Report Page Logic (Form Validation) ---
    // This assumes report.php has a form with id="report-form",
    // and inputs with ids "title", "description", "category", "image".
    const reportForm = document.getElementById('report-form');
    if (reportForm) {
        reportForm.addEventListener('submit', (event) => {
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const categorySelect = document.getElementById('category'); // This maps to 'status' in PHP/DB
            const imageInput = document.getElementById('image');

            // 1. Check if title, description, and status are filled
            if (!titleInput.value.trim()) {
                showAlert('Please enter a title for the item.', 'error');
                event.preventDefault(); // Stop form submission
                return;
            }
            if (!descriptionInput.value.trim()) {
                showAlert('Please enter a description for the item.', 'error');
                event.preventDefault();
                return;
            }
            if (!categorySelect.value) { // Check if a category (status) is selected
                showAlert('Please select a status (Lost/Found) for the item.', 'error');
                event.preventDefault();
                return;
            }

            // 2. If an image is uploaded, check its file type
            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showAlert('Only JPG, JPEG, PNG, and GIF image formats are allowed.', 'error');
                    event.preventDefault();
                    return;
                }
            }

            // If all checks pass, the form will submit normally (handled by PHP)
            // showAlert('Form is valid. Submitting to PHP...', 'success'); // Optional: for debugging
        });

        // --- Image Preview (copied from previous versions for UI, purely client-side) ---
        const imageInput = document.getElementById('image');
        const imagePreview = document.querySelector('#image-preview img');
        const imageUploadArea = document.querySelector('.image-upload-area');

        if (imageInput) {
            imageInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.src = '';
                    imagePreview.style.display = 'none';
                }
            });
        }

        // Drag and Drop (also purely client-side UI, no backend interaction)
        if (imageUploadArea) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                imageUploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                imageUploadArea.addEventListener(eventName, () => imageUploadArea.classList.add('highlight'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                imageUploadArea.addEventListener(eventName, () => imageUploadArea.classList.remove('highlight'), false);
            });

            imageUploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                imageInput.files = files;
                const event = new Event('change');
                imageInput.dispatchEvent(event);
            }

            imageUploadArea.addEventListener('click', () => {
                imageInput.click();
            });
        }
    }


    // --- Index Page Logic (Search Functionality) ---
    // This assumes index.php has an input with id="search-input"
    // and item cards inside a container with id="items-container", each with class="item-card"
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            const itemsContainer = document.getElementById('items-container');
            const itemCards = itemsContainer ? itemsContainer.getElementsByClassName('item-card') : [];
            let foundItems = false;

            Array.from(itemCards).forEach(card => {
                // Assuming the title is within an <h3> tag inside the item-card
                const titleElement = card.querySelector('h3');
                if (titleElement) {
                    const itemTitle = titleElement.textContent.toLowerCase();
                    if (itemTitle.includes(searchTerm)) {
                        card.style.display = ''; // Show the card
                        foundItems = true;
                    } else {
                        card.style.display = 'none'; // Hide the card
                    }
                }
            });

            // Handle "No items found" message
            const noItemsMessage = document.getElementById('no-items-message');
            if (noItemsMessage) {
                if (foundItems) {
                    noItemsMessage.style.display = 'none';
                } else {
                    noItemsMessage.style.display = 'block';
                }
            }
        });
    }

    // --- Other client-side UI enhancements from previous versions ---
    // (e.g., custom select arrow, notification container styling etc. are in style.css)
    // No JS needed here for those as they are purely CSS-driven or simple HTML.
});