// =====================================================
// NewsLanka — Frontend JS
// =====================================================

(function () {
    'use strict';

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => {
            const targetId = link.getAttribute('href').slice(1);
            if (!targetId) return;
            const target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Live search input — debounce and redirect
    const searchInput = document.getElementById('liveSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                const q = searchInput.value.trim();
                if (q.length >= 2) {
                    window.location.href = 'search.php?q=' + encodeURIComponent(q);
                }
            }
        });
    }

    // Newsletter signup placeholder
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', e => {
            e.preventDefault();
            const email = newsletterForm.querySelector('input[type="email"]').value;
            if (email) {
                alert('Thank you for subscribing! (Demo)');
                newsletterForm.reset();
            }
        });
    }

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Back to top button
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.style.display = window.scrollY > 400 ? 'block' : 'none';
        });
        backToTop.addEventListener('click', e => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Comment form character counter
    const commentField = document.getElementById('commentField');
    const commentCounter = document.getElementById('commentCounter');
    if (commentField && commentCounter) {
        const max = 1000;
        commentField.addEventListener('input', () => {
            commentCounter.textContent = commentField.value.length + ' / ' + max;
        });
    }
})();
