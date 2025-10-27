/**
 * Banner & Promo Slider with Smooth Animations
 * GoodZStore - 2025
 */

class Slider {
    constructor(sliderId, options = {}) {
        this.slider = document.getElementById(sliderId);
        if (!this.slider) return;
        
        this.slides = this.slider.querySelectorAll('.slide, .promo-slide');
        this.currentIndex = 0;
        this.isTransitioning = false;
        
        // Options
        this.autoPlayDelay = options.autoPlayDelay || 5000;
        this.transitionDuration = options.transitionDuration || 600;
        this.pauseOnHover = options.pauseOnHover !== false;
        this.effect = options.effect || 'fade'; // fade, slide, scale
        
        this.init();
    }
    
    init() {
        if (this.slides.length <= 1) return;
        
        // Set initial state
        this.slides[0].classList.add('active');
        
        // Start autoplay
        this.startAutoPlay();
        
        // Pause on hover
        if (this.pauseOnHover) {
            this.slider.addEventListener('mouseenter', () => this.stopAutoPlay());
            this.slider.addEventListener('mouseleave', () => this.startAutoPlay());
        }
        
        // Swipe support for mobile
        this.addSwipeSupport();
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
        });
    }
    
    showSlide(index, direction = 1) {
        if (this.isTransitioning) return;
        
        // Wrap around
        if (index >= this.slides.length) index = 0;
        if (index < 0) index = this.slides.length - 1;
        
        if (index === this.currentIndex) return;
        
        this.isTransitioning = true;
        
        const currentSlide = this.slides[this.currentIndex];
        const nextSlide = this.slides[index];
        
        // Apply animation based on effect
        this.applyTransition(currentSlide, nextSlide, direction);
        
        // Update dots if they exist
        this.updateDots(index);
        
        this.currentIndex = index;
        
        setTimeout(() => {
            this.isTransitioning = false;
        }, this.transitionDuration);
    }
    
    applyTransition(currentSlide, nextSlide, direction) {
        switch (this.effect) {
            case 'slide':
                this.slideTransition(currentSlide, nextSlide, direction);
                break;
            case 'scale':
                this.scaleTransition(currentSlide, nextSlide);
                break;
            case 'fade':
            default:
                this.fadeTransition(currentSlide, nextSlide);
                break;
        }
    }
    
    fadeTransition(currentSlide, nextSlide) {
        currentSlide.style.opacity = '1';
        nextSlide.style.opacity = '0';
        nextSlide.classList.add('active');
        nextSlide.style.display = 'block';
        
        requestAnimationFrame(() => {
            currentSlide.style.transition = `opacity ${this.transitionDuration}ms ease-in-out`;
            nextSlide.style.transition = `opacity ${this.transitionDuration}ms ease-in-out`;
            
            currentSlide.style.opacity = '0';
            nextSlide.style.opacity = '1';
        });
        
        setTimeout(() => {
            currentSlide.classList.remove('active');
            currentSlide.style.display = 'none';
            currentSlide.style.transition = '';
            nextSlide.style.transition = '';
        }, this.transitionDuration);
    }
    
    slideTransition(currentSlide, nextSlide, direction) {
        const slideWidth = this.slider.offsetWidth;
        
        nextSlide.style.transform = `translateX(${direction * 100}%)`;
        nextSlide.classList.add('active');
        nextSlide.style.display = 'block';
        
        requestAnimationFrame(() => {
            currentSlide.style.transition = `transform ${this.transitionDuration}ms cubic-bezier(0.645, 0.045, 0.355, 1)`;
            nextSlide.style.transition = `transform ${this.transitionDuration}ms cubic-bezier(0.645, 0.045, 0.355, 1)`;
            
            currentSlide.style.transform = `translateX(${-direction * 100}%)`;
            nextSlide.style.transform = 'translateX(0)';
        });
        
        setTimeout(() => {
            currentSlide.classList.remove('active');
            currentSlide.style.display = 'none';
            currentSlide.style.transform = '';
            currentSlide.style.transition = '';
            nextSlide.style.transition = '';
        }, this.transitionDuration);
    }
    
    scaleTransition(currentSlide, nextSlide) {
        nextSlide.style.transform = 'scale(0.8)';
        nextSlide.style.opacity = '0';
        nextSlide.classList.add('active');
        nextSlide.style.display = 'block';
        
        requestAnimationFrame(() => {
            currentSlide.style.transition = `all ${this.transitionDuration}ms ease-out`;
            nextSlide.style.transition = `all ${this.transitionDuration}ms ease-out`;
            
            currentSlide.style.transform = 'scale(1.2)';
            currentSlide.style.opacity = '0';
            nextSlide.style.transform = 'scale(1)';
            nextSlide.style.opacity = '1';
        });
        
        setTimeout(() => {
            currentSlide.classList.remove('active');
            currentSlide.style.display = 'none';
            currentSlide.style.transform = '';
            currentSlide.style.opacity = '';
            currentSlide.style.transition = '';
            nextSlide.style.transition = '';
        }, this.transitionDuration);
    }
    
    updateDots(index) {
        const dots = this.slider.querySelectorAll('.dot');
        if (dots.length === 0) return;
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }
    
    next() {
        this.showSlide(this.currentIndex + 1, 1);
    }
    
    prev() {
        this.showSlide(this.currentIndex - 1, -1);
    }
    
    goTo(index) {
        const direction = index > this.currentIndex ? 1 : -1;
        this.showSlide(index, direction);
    }
    
    startAutoPlay() {
        this.stopAutoPlay();
        this.autoPlayTimer = setInterval(() => this.next(), this.autoPlayDelay);
    }
    
    stopAutoPlay() {
        if (this.autoPlayTimer) {
            clearInterval(this.autoPlayTimer);
            this.autoPlayTimer = null;
        }
    }
    
    addSwipeSupport() {
        let startX = 0;
        let startY = 0;
        let isSwiping = false;
        
        this.slider.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isSwiping = true;
        }, { passive: true });
        
        this.slider.addEventListener('touchmove', (e) => {
            if (!isSwiping) return;
            
            const diffX = e.touches[0].clientX - startX;
            const diffY = e.touches[0].clientY - startY;
            
            // Prevent vertical scroll if horizontal swipe
            if (Math.abs(diffX) > Math.abs(diffY)) {
                e.preventDefault();
            }
        }, { passive: false });
        
        this.slider.addEventListener('touchend', (e) => {
            if (!isSwiping) return;
            
            const endX = e.changedTouches[0].clientX;
            const diffX = endX - startX;
            
            if (Math.abs(diffX) > 50) { // Minimum swipe distance
                if (diffX > 0) {
                    this.prev();
                } else {
                    this.next();
                }
            }
            
            isSwiping = false;
        }, { passive: true });
    }
}

// Initialize sliders when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Banner Slider
    const bannerSlider = new Slider('bannerSlider', {
        autoPlayDelay: 5000,
        transitionDuration: 800,
        effect: 'fade',
        pauseOnHover: true
    });
    
    // Promo Slider
    const promoSlider = new Slider('promoSlider', {
        autoPlayDelay: 4000,
        transitionDuration: 700,
        effect: 'slide',
        pauseOnHover: true
    });
    
    // Button controls for Banner
    const bannerPrev = document.querySelector('.banner-prev');
    const bannerNext = document.querySelector('.banner-next');
    if (bannerPrev) bannerPrev.addEventListener('click', () => bannerSlider.prev());
    if (bannerNext) bannerNext.addEventListener('click', () => bannerSlider.next());
    
    // Button controls for Promo
    const promoPrev = document.querySelector('.promo-prev');
    const promoNext = document.querySelector('.promo-next');
    if (promoPrev) promoPrev.addEventListener('click', () => promoSlider.prev());
    if (promoNext) promoNext.addEventListener('click', () => promoSlider.next());
    
    // Dot navigation for Banner
    const bannerDots = document.querySelectorAll('#bannerSlider .dot');
    bannerDots.forEach((dot, index) => {
        dot.addEventListener('click', () => bannerSlider.goTo(index));
    });
    
    // Export for global access
    window.bannerSlider = bannerSlider;
    window.promoSlider = promoSlider;
    
    // Global functions for inline onclick handlers (backward compatibility)
    window.changeSlide = function(direction) {
        if (window.bannerSlider) {
            if (direction > 0) window.bannerSlider.next();
            else window.bannerSlider.prev();
        }
    };
    
    window.changePromoSlide = function(direction) {
        if (window.promoSlider) {
            if (direction > 0) window.promoSlider.next();
            else window.promoSlider.prev();
        }
    };
    
    window.currentSlide = function(index) {
        if (window.bannerSlider) {
            window.bannerSlider.goTo(index);
        }
    };
});

// Smooth parallax effect on scroll (optional enhancement)
window.addEventListener('scroll', function() {
    const banners = document.querySelectorAll('.slide.active img, .promo-slide.active img');
    const scrolled = window.pageYOffset;
    
    banners.forEach(banner => {
        const rect = banner.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            const speed = 0.3;
            banner.style.transform = `translateY(${scrolled * speed}px)`;
        }
    });
});
