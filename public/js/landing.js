/**
 * U-LITS Landing Page
 * Handles navigation, FAQ accordion, contact form, scroll animations
 */
(function () {
    'use strict';

    /* ---- Mobile Navigation ---- */
    function initMobileNav() {
        var toggle = document.getElementById('navToggle');
        var menu = document.getElementById('navMenu');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function () {
            toggle.classList.toggle('active');
            menu.classList.toggle('active');
        });

        // Close menu when a link is clicked
        menu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                toggle.classList.remove('active');
                menu.classList.remove('active');
            });
        });
    }

    /* ---- Sticky Header Shadow ---- */
    function initHeaderScroll() {
        var header = document.getElementById('header');
        if (!header) return;

        function onScroll() {
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---- Smooth Scroll ---- */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var href = this.getAttribute('href');
                if (href === '#') return;
                var target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    var headerH = document.querySelector('.header') ? document.querySelector('.header').offsetHeight : 0;
                    var top = target.getBoundingClientRect().top + window.pageYOffset - headerH - 12;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                }
            });
        });
    }

    /* ---- FAQ Accordion ---- */
    function initFAQ() {
        document.querySelectorAll('.faq-question').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.closest('.faq-item');
                var isActive = item.classList.contains('active');

                // Close all first
                document.querySelectorAll('.faq-item.active').forEach(function (open) {
                    open.classList.remove('active');
                });

                // Toggle clicked
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    }

    /* ---- Contact Form ---- */
    function initContactForm() {
        var form = document.getElementById('contactForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;

            var data = {};
            new FormData(form).forEach(function (val, key) { data[key] = val; });

            fetch('/api/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': data._token || ''
                },
                body: JSON.stringify(data)
            })
            .then(function (res) {
                if (res.ok) {
                    showNotification('Thank you! Your message has been sent.', 'success');
                    form.reset();
                } else {
                    showNotification('Failed to send message. Please try again.', 'error');
                }
            })
            .catch(function () {
                showNotification('Thank you! We will get back to you soon.', 'success');
                form.reset();
            })
            .finally(function () {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    /* ---- Toast Notification ---- */
    function showNotification(message, type) {
        var el = document.createElement('div');
        el.className = 'notification notification-' + (type || 'success');
        el.textContent = message;
        document.body.appendChild(el);

        setTimeout(function () {
            el.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(function () { el.remove(); }, 300);
        }, 4000);
    }

    /* ---- Scroll Animations ---- */
    function initScrollAnimations() {
        var elements = document.querySelectorAll('.animate-on-scroll');
        if (!elements.length) return;

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

            elements.forEach(function (el) { observer.observe(el); });
        } else {
            // Fallback: show all immediately
            elements.forEach(function (el) { el.classList.add('visible'); });
        }
    }

    /* ---- Initialize ---- */
    function init() {
        initMobileNav();
        initHeaderScroll();
        initSmoothScroll();
        initFAQ();
        initContactForm();
        initScrollAnimations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
