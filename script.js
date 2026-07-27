/**
 * CampusBite - Client-side JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Animate canteen cards on scroll
    const cards = document.querySelectorAll('.canteen-card, .content-card, .review-item');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1 }
        );

        cards.forEach((card) => observer.observe(card));
    } else {
        cards.forEach((card) => card.classList.add('fade-in'));
    }

    // Auto-dismiss success alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible.auto-dismiss');
    alerts.forEach((alert) => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm before submitting complaint
    const complaintForm = document.getElementById('complaintForm');
    if (complaintForm) {
        complaintForm.addEventListener('submit', function (e) {
            const message = document.getElementById('message');
            if (message && message.value.trim().length < 10) {
                e.preventDefault();
                alert('Please write a complaint message of at least 10 characters.');
            }
        });
    }

    // Review form validation
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            const rating = reviewForm.querySelector('input[name="rating"]:checked');
            const comment = document.getElementById('comment');

            if (!rating) {
                e.preventDefault();
                alert('Please select a star rating.');
                return;
            }

            if (comment && comment.value.trim().length < 5) {
                e.preventDefault();
                alert('Please write a review of at least 5 characters.');
            }
        });
    }
});
