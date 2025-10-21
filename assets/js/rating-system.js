/**
 * TechEssentials Pro - Système de Rating avec Pagination Progressive
 * Fichier: assets/js/rating-system.js
 */

class ProductRatingSystem {
    constructor(productId) {
        this.productId = productId;
        this.selectedRating = 0;
        
        // Propriétés de pagination
        this.reviewsOffset = 0;
        this.reviewsLimit = 5;
        this.hasMoreReviews = false;
        
        this.init();
    }
    
    init() {
        this.setupStarRating();
        this.setupForm();
        this.loadExistingRating();
    }
    
    setupStarRating() {
        const stars = document.querySelectorAll('.rating-input .star');
        const ratingValue = document.querySelector('.rating-value');
        
        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', () => {
                this.highlightStars(index + 1);
            });
            
            star.addEventListener('click', () => {
                this.selectedRating = index + 1;
                this.highlightStars(this.selectedRating);
                ratingValue.textContent = this.getRatingLabel(this.selectedRating);
            });
        });
        
        const ratingInput = document.querySelector('.rating-input');
        ratingInput.addEventListener('mouseleave', () => {
            this.highlightStars(this.selectedRating);
        });
    }
    
    highlightStars(count) {
        const stars = document.querySelectorAll('.rating-input .star');
        stars.forEach((star, index) => {
            if (index < count) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    getRatingLabel(rating) {
        const labels = {
            1: 'Décevant',
            2: 'Passable',
            3: 'Satisfaisant',
            4: 'Très bien',
            5: 'Excellent'
        };
        return labels[rating] || 'Sélectionnez une note';
    }
    
    setupForm() {
        const form = document.getElementById('rating-form');
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitRating();
        });
    }
    
    async loadExistingRating() {
        try {
            const response = await fetch(
                `api/get-rating.php?product_id=${this.productId}&limit=${this.reviewsLimit}&offset=${this.reviewsOffset}`
            );
            
            if (!response.ok) {
                throw new Error('Erreur de chargement');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.displayRatingStats(data);
                this.displayReviews(data.reviews, data.pagination);
                this.hasMoreReviews = data.pagination.hasMore;
            } else {
                console.error('Erreur API:', data.message);
            }
            
        } catch (error) {
            console.error('Erreur chargement rating:', error);
        }
    }
    
    displayRatingStats(data) {
        const avgElement = document.querySelector('.rating-average');
        if (avgElement) {
            avgElement.textContent = data.average.toFixed(1);
        }
        
        const countElement = document.querySelector('.rating-count');
        if (countElement) {
            countElement.textContent = `${data.count} avis`;
        }
        
        const starsElement = document.querySelector('.rating-stars');
        if (starsElement) {
            starsElement.innerHTML = this.generateStarsHTML(data.average);
        }
        
        if (data.distribution) {
            this.displayDistribution(data.distribution);
        }
    }
    
    generateStarsHTML(rating) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) {
                html += '<span class="star filled">★</span>';
            } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
                html += '<span class="star half">★</span>';
            } else {
                html += '<span class="star">★</span>';
            }
        }
        return html;
    }
    
    displayDistribution(distribution) {
        for (let i = 5; i >= 1; i--) {
            const barElement = document.querySelector(`#rating-bar-${i}`);
            const countElement = document.querySelector(`#rating-count-${i}`);
            
            if (barElement && distribution[i]) {
                barElement.style.width = distribution[i].percentage + '%';
            }
            
            if (countElement && distribution[i]) {
                countElement.textContent = distribution[i].count;
            }
        }
    }
    
    displayReviews(reviews, pagination) {
        const container = document.getElementById('existing-reviews');
        if (!container) return;
        
        if (!reviews || reviews.length === 0) {
            if (this.reviewsOffset === 0) {
                container.innerHTML = '<p class="no-reviews">Aucun avis pour le moment. Soyez le premier à donner votre avis !</p>';
            }
            return;
        }
        
        const isFirstLoad = this.reviewsOffset === 0;
        
        const reviewsHTML = reviews.map(review => `
            <div class="review-item">
                <div class="review-header">
                    <div class="review-rating">
                        ${this.generateStarsHTML(review.rating)}
                    </div>
                    <div class="review-date">${review.date}</div>
                </div>
                ${review.hasComment ? `<div class="review-comment">${this.escapeHTML(review.comment)}</div>` : ''}
            </div>
        `).join('');
        
        if (isFirstLoad) {
            container.innerHTML = reviewsHTML;
        } else {
            container.insertAdjacentHTML('beforeend', reviewsHTML);
        }
        
        this.updateLoadMoreButton(pagination);
    }
    
    updateLoadMoreButton(pagination) {
        let loadMoreBtn = document.getElementById('load-more-reviews');
        
        if (!loadMoreBtn) {
            const container = document.getElementById('existing-reviews');
            if (!container) return;
            
            loadMoreBtn = document.createElement('button');
            loadMoreBtn.id = 'load-more-reviews';
            loadMoreBtn.className = 'load-more-btn';
            loadMoreBtn.textContent = 'Voir plus d\'avis';
            loadMoreBtn.onclick = () => this.loadMoreReviews();
            
            container.parentElement.appendChild(loadMoreBtn);
        }
        
        if (pagination.hasMore) {
            loadMoreBtn.style.display = 'block';
            const remaining = pagination.total - pagination.currentOffset - pagination.loaded;
            loadMoreBtn.textContent = `Voir plus d'avis (${remaining} restant${remaining > 1 ? 's' : ''})`;
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
    
    async loadMoreReviews() {
        this.reviewsOffset += this.reviewsLimit;
        
        const loadMoreBtn = document.getElementById('load-more-reviews');
        if (loadMoreBtn) {
            loadMoreBtn.textContent = 'Chargement...';
            loadMoreBtn.disabled = true;
        }
        
        try {
            const response = await fetch(
                `api/get-rating.php?product_id=${this.productId}&limit=${this.reviewsLimit}&offset=${this.reviewsOffset}`
            );
            
            if (!response.ok) {
                throw new Error('Erreur de chargement');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.displayReviews(data.reviews, data.pagination);
                this.hasMoreReviews = data.pagination.hasMore;
            }
            
        } catch (error) {
            console.error('Erreur chargement avis:', error);
            this.showMessage('Erreur lors du chargement des avis', 'error');
        } finally {
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
            }
        }
    }
    
    async submitRating() {
        if (this.selectedRating === 0) {
            this.showMessage('Veuillez sélectionner une note', 'error');
            return;
        }
        
        const comment = document.getElementById('rating-comment').value.trim();
        
        // Envoyer en JSON au lieu de FormData
        const data = {
            product_id: this.productId,
            rating: this.selectedRating,
            comment: comment
        };
        
        const submitBtn = document.querySelector('#rating-form button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Envoi en cours...';
        
        try {
            const response = await fetch('api/submit-rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error('Erreur lors de l\'envoi');
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showMessage('Merci pour votre avis !', 'success');
                this.resetForm();
                this.reviewsOffset = 0;
                this.loadExistingRating();
            } else {
                this.showMessage(result.message || 'Erreur lors de l\'envoi', 'error');
            }
            
        } catch (error) {
            console.error('Erreur soumission:', error);
            this.showMessage('Une erreur est survenue', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
    
    resetForm() {
        this.selectedRating = 0;
        this.highlightStars(0);
        document.querySelector('.rating-value').textContent = 'Sélectionnez une note';
        document.getElementById('rating-comment').value = '';
    }
    
    showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `rating-message ${type}`;
        messageDiv.textContent = message;
        
        const form = document.getElementById('rating-form');
        form.insertAdjacentElement('beforebegin', messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
    
    escapeHTML(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const productId = document.body.dataset.productId || 'demo';
    new ProductRatingSystem(productId);
});