// =====================================================
// NewsLanka Admin Panel JS
// =====================================================

(function () {
    'use strict';

    // Confirm delete dialogs
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            const msg = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // Auto-generate slug from title
    const titleInput = document.getElementById('titleInput');
    const slugInput = document.getElementById('slugInput');
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', () => {
            if (!slugInput.dataset.touched) {
                slugInput.value = titleInput.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');
            }
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.touched = '1';
        });
    }

    // Image preview on file select
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => {
                    imagePreview.src = ev.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            if (window.bootstrap) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 4000);
    });
})();
