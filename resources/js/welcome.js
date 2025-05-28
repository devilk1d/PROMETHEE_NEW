// Welcome Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Navigation scroll effect
    const nav = document.querySelector('.main-nav');
    
    function handleScroll() {
        if (window.scrollY > 50) {
            nav.style.background = 'rgba(255, 255, 255, 0.98)';
            nav.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        } else {
            nav.style.background = 'rgba(255, 255, 255, 0.95)';
            nav.style.boxShadow = 'none';
        }
    }
    
    window.addEventListener('scroll', handleScroll);
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Animate elements when they come into view
    const animateElements = document.querySelectorAll('.feature-card, .process-step, .stat-item');
    animateElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(element);
    });
    
    // Counter animation for stats
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 20);
    }
    
    // Trigger counter animation when stats come into view
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumber = entry.target.querySelector('.stat-number');
                const text = statNumber.textContent;
                if (text.includes('%')) {
                    const number = parseInt(text);
                    if (!isNaN(number)) {
                        statNumber.textContent = '0%';
                        animateCounter(statNumber, number);
                        statNumber.textContent = number + '%';
                    }
                }
                statsObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.stat-item').forEach(stat => {
        statsObserver.observe(stat);
    });
    
    // Parallax effect for hero circles
    function handleParallax() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        document.querySelectorAll('.hero-circle').forEach((circle, index) => {
            const speed = (index + 1) * 0.3;
            circle.style.transform = `translateY(${rate * speed}px)`;
        });
    }
    
    window.addEventListener('scroll', handleParallax);
    
    // Mobile menu handling
    const mobileBreakpoint = 768;
    
    function handleResize() {
        if (window.innerWidth <= mobileBreakpoint) {
            // Mobile specific adjustments
            document.querySelectorAll('.btn-hero').forEach(btn => {
                btn.style.width = '100%';
                btn.style.maxWidth = '300px';
            });
        } else {
            // Desktop adjustments
            document.querySelectorAll('.btn-hero').forEach(btn => {
                btn.style.width = 'auto';
                btn.style.maxWidth = 'none';
            });
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Initial call
    
    // Button hover effects
    document.querySelectorAll('.btn-hero, .btn-cta, .btn-nav').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Feature card interactions
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-5px)';
        });
    });
    
    // Process step animations
    document.querySelectorAll('.process-step').forEach((step, index) => {
        step.style.animationDelay = `${index * 0.2}s`;
    });
    
    // Gradient animation for highlighted text
    const gradientElements = document.querySelectorAll('.hero-highlight, .text-gradient');
    gradientElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.backgroundSize = '200% 200%';
            this.style.animation = 'gradientShift 2s ease infinite';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.animation = 'none';
            this.style.backgroundSize = '100% 100%';
        });
    });
    
    // Add gradient animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    `;
    document.head.appendChild(style);
    
    // Loading animation completion
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 100);
    
    // Performance optimization: Debounce scroll events
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    const debouncedScroll = debounce(() => {
        handleScroll();
        handleParallax();
    }, 10);
    
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('scroll', handleParallax);
    window.addEventListener('scroll', debouncedScroll);
});