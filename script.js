// JavaScript for interactive elements
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== CAROUSEL FUNCTIONALITY =====
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;
    let autoplayInterval;
    
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => slide.style.opacity = '0');
        
        // Update indicators
        indicators.forEach((indicator, i) => {
            if (i === index) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
        
        // Show current slide
        slides[index].style.opacity = '1';
        currentSlide = index;
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        showSlide(currentSlide);
    }
    
    function startAutoplay() {
        autoplayInterval = setInterval(nextSlide, 4000); // Change slide every 4 seconds
    }
    
    function resetAutoplay() {
        clearInterval(autoplayInterval);
        startAutoplay();
    }
    
    // Indicator buttons - Click to navigate
    indicators.forEach(indicator => {
        indicator.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            showSlide(index);
            resetAutoplay();
        });
    });
    
    // Initialize first slide and start autoplay
    showSlide(0);
    startAutoplay();
    
    // ===== ADD TO CART BUTTONS =====
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartCount = document.querySelector('.bg-accent');
    let count = 3;
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            count++;
            cartCount.textContent = count;
            
            // Animation effect
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('bg-electric');
            button.classList.add('bg-green-500');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('bg-green-500');
                button.classList.add('bg-electric');
            }, 1500);
        });
    });
    
    // ===== SEARCH FUNCTIONALITY =====
    const searchInput = document.querySelector('input[type="text"]');
    const searchButton = document.querySelector('.fa-search').parentElement;
    
    searchButton.addEventListener('click', function() {
        if (searchInput.value.trim() !== '') {
            alert(`Searching for: ${searchInput.value}`);
        } else {
            alert('Please enter a search term');
        }
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (searchInput.value.trim() !== '') {
                alert(`Searching for: ${searchInput.value}`);
            } else {
                alert('Please enter a search term');
            }
        }
    });
    
    // ===== COUNTDOWN TIMER FOR FLASH SALE =====
    function updateCountdown() {
        const countdownDate = new Date();
        countdownDate.setDate(countdownDate.getDate() + 2); // 2 days from now
        
        const now = new Date().getTime();
        const distance = countdownDate - now;
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById('days').textContent = days.toString().padStart(2, '0');
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    }
    
    // Update countdown every minute
    updateCountdown();
    setInterval(updateCountdown, 60000);
    
    // ===== NEWSLETTER SUBSCRIPTION =====
    const newsletterButton = document.querySelector('.bg-accent');
    newsletterButton.addEventListener('click', function() {
        const emailInput = document.querySelector('input[type="email"]');
        if (emailInput.value.trim() !== '' && emailInput.value.includes('@')) {
            alert('Thank you for subscribing to our newsletter!');
            emailInput.value = '';
        } else {
            alert('Please enter a valid email address');
        }
    });
    
    // ===== DARK MODE TOGGLE =====
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;
    
    // Check for saved dark mode preference or default to system preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true' || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches && localStorage.getItem('darkMode') !== 'false');
    
    // Set initial state
    if (isDarkMode) {
        htmlElement.classList.add('dark');
        darkModeToggle.checked = true;
    }
    
    // Toggle dark mode
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            htmlElement.classList.add('dark');
            localStorage.setItem('darkMode', 'true');
        } else {
            htmlElement.classList.remove('dark');
            localStorage.setItem('darkMode', 'false');
        }
    });
});
