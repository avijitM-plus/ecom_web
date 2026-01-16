// Centralized, defensive JavaScript for interactive elements
document.addEventListener('DOMContentLoaded', function () {

    // ===== CAROUSEL FUNCTIONALITY (guarded) =====
    (function initCarousel() {
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;
        let currentSlide = 0;
        let autoplayInterval;

        if (totalSlides === 0 || indicators.length === 0) return;

        function showSlide(index) {
            slides.forEach(slide => slide.style.opacity = '0');
            indicators.forEach((indicator, i) => indicator.classList.toggle('active', i === index));
            slides[index].style.opacity = '1';
            currentSlide = index;
        }

        function nextSlide() { currentSlide = (currentSlide + 1) % totalSlides; showSlide(currentSlide); }
        function startAutoplay() { autoplayInterval = setInterval(nextSlide, 4000); }
        function resetAutoplay() { clearInterval(autoplayInterval); startAutoplay(); }

        indicators.forEach(indicator => indicator.addEventListener('click', function () { const index = parseInt(this.getAttribute('data-index')) || 0; showSlide(index); resetAutoplay(); }));

        showSlide(0);
        startAutoplay();
    })();

    // ===== ADD TO CART (guarded) =====
    (function initAddToCart() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        const cartCountEl = document.querySelector('.bg-accent');
        if (!addToCartButtons.length || !cartCountEl) return;
        let count = parseInt(cartCountEl.textContent) || 0;

        addToCartButtons.forEach(button => button.addEventListener('click', function () {
            count++;
            cartCountEl.textContent = count;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('bg-electric');
            button.classList.add('bg-green-500');
            setTimeout(() => { button.innerHTML = originalHTML; button.classList.remove('bg-green-500'); button.classList.add('bg-electric'); }, 1500);
        }));
    })();

    // ===== SEARCH =====
    // Note: Search is handled by the form's native submission to products.php
    // No additional JavaScript needed for basic search functionality

    // ===== COUNTDOWN (guarded) =====
    (function initCountdown() {
        const daysEl = document.getElementById('days');
        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        if (!daysEl || !hoursEl || !minutesEl) return;

        function updateCountdown() {
            const countdownDate = new Date();
            countdownDate.setDate(countdownDate.getDate() + 2);
            const now = Date.now();
            const distance = countdownDate - now;
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            daysEl.textContent = String(days).padStart(2, '0');
            hoursEl.textContent = String(hours).padStart(2, '0');
            minutesEl.textContent = String(minutes).padStart(2, '0');
        }
        updateCountdown();
        setInterval(updateCountdown, 60000);
    })();

    // ===== NEWSLETTER (guarded) =====
    (function initNewsletter() {
        const newsletterButton = document.querySelector('.bg-accent');
        const emailInput = document.querySelector('input[type="email"]');
        if (!newsletterButton || !emailInput) return;
        newsletterButton.addEventListener('click', function () { if (emailInput.value.trim() !== '' && emailInput.value.includes('@')) { alert('Thank you for subscribing to our newsletter!'); emailInput.value = ''; } else alert('Please enter a valid email address'); });
    })();

    // ===== MOBILE MENU (site-wide, guarded) =====
    (function initMobileMenu() {
        const menuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        if (!menuButton || !mobileMenu) return;
        function setAriaExpanded(el, state) { el.setAttribute('aria-expanded', String(state)); }
        menuButton.addEventListener('click', function () {
            const isOpen = !mobileMenu.classList.contains('hidden');
            mobileMenu.classList.toggle('hidden');
            setAriaExpanded(menuButton, !isOpen);
            const icon = menuButton.querySelector('i');
            if (icon) {
                if (!isOpen) { icon.classList.remove('fa-bars'); icon.classList.add('fa-xmark'); icon.classList.remove('fa-times'); }
                else { icon.classList.remove('fa-xmark'); icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); }
            }
        });
    })();

    // ===== DARK MODE (robust, stored-first) =====
    (function initDarkMode() {
        const htmlElement = document.documentElement;
        const darkModeToggle = document.getElementById('darkModeToggle');

        // Get stored preference or system preference
        const stored = localStorage.getItem('darkMode');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = stored === 'true' || (stored === null && prefersDark);

        // Apply initial state
        if (isDark) {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }

        // Set toggle state if it exists
        if (darkModeToggle) {
            darkModeToggle.checked = isDark;

            // Listen to toggle changes
            darkModeToggle.addEventListener('change', function () {
                if (this.checked) {
                    htmlElement.classList.add('dark');
                    localStorage.setItem('darkMode', 'true');
                } else {
                    htmlElement.classList.remove('dark');
                    localStorage.setItem('darkMode', 'false');
                }
            });
        }

        // Listen to system preference changes (only if user hasn't set a preference)
        try {
            if (window.matchMedia) {
                const mq = window.matchMedia('(prefers-color-scheme: dark)');
                const handler = function (e) {
                    if (localStorage.getItem('darkMode') === null) {
                        htmlElement.classList.toggle('dark', e.matches);
                        if (darkModeToggle) darkModeToggle.checked = e.matches;
                    }
                };
                if (mq.addEventListener) mq.addEventListener('change', handler);
                else if (mq.addListener) mq.addListener(handler);
            }
        } catch (err) {
            // Ignore errors
        }
    })();

});
