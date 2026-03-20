/**
 * U-LITS Landing Page JavaScript
 * Handles interactivity and smooth user experience
 */

(function() {
    'use strict';

    // ================================================================
    // MOBILE NAVIGATION TOGGLE
    // ================================================================
    function initMobileNav() {
        const navToggle = document.querySelector('.nav-toggle');
        const navbar = document.querySelector('.navbar');

        if (!navToggle) return;

        navToggle.addEventListener('click', () => {
            navbar.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close menu when link is clicked
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navbar.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });
    }

    // ================================================================
    // DETAIL TABS
    // ================================================================
    function initDetailTabs() {
        const tabs = document.querySelectorAll('.detail-tab');
        const contents = document.querySelectorAll('.detail-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');

                // Remove active from all
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                // Add active to clicked
                tab.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });
    }

    // ================================================================
    // CONTACT FORM HANDLING
    // ================================================================
    function initContactForm() {
        const form = document.getElementById('contactForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Add visual feedback
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;

            try {
                // You would replace this with actual API endpoint
                const response = await fetch('/api/contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    showNotification('Message sent successfully!', 'success');
                    form.reset();
                } else {
                    showNotification('Failed to send message. Please try again.', 'error');
                }
            } catch (error) {
                console.log('Form would be submitted to server');
                showNotification('Thank you! We will get back to you soon.', 'success');
                form.reset();
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // ================================================================
    // NOTIFICATION SYSTEM
    // ================================================================
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: ${type === 'success' ? '#4caf50' : '#f44336'};
            color: white;
            padding: 16px 24px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        `;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // ================================================================
    // SMOOTH SCROLL NAVIGATION
    // ================================================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // ================================================================
    // SCROLL ANIMATIONS
    // ================================================================
    function initScrollAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const animationElements = document.querySelectorAll(
            '.about-card, .feature-item, .use-case-card, .impact-card, .register-card'
        );

        if (animationElements.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        animationElements.forEach(el => {
            observer.observe(el);
        });
    }

    // ================================================================
    // INITIALIZE ALL
    // ================================================================
    function init() {
        // Add animation styles to document
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);

        // Initialize components
        initMobileNav();
        initDetailTabs();
        initContactForm();
        initSmoothScroll();
        initScrollAnimations();

        console.log('U-LITS Landing Page Initialized');
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
