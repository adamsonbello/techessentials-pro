<?php
/**
 * TechEssentials Pro - Template Page Deals
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}
?>

<!-- Page Header -->
<section class="deals-header">
    <div class="container">
        <div class="deals-header-content">
            <h1>💰 Best Tech Deals</h1>
            <p>Exclusive discounts and deals on the best tech accessories for remote workers. Save money while upgrading your workspace!</p>
            
            <div class="deals-stats">
                <div class="deal-stat">
                    <div class="stat-number"><?= count($deals ?? []) ?></div>
                    <div class="stat-label">Active Deals</div>
                </div>
                <div class="deal-stat">
                    <div class="stat-number">60%</div>
                    <div class="stat-label">Max Savings</div>
                </div>
                <div class="deal-stat">
                    <div class="stat-number">24h</div>
                    <div class="stat-label">Updated</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Deal Alerts Signup -->
<section class="deal-alerts">
    <div class="container">
        <div class="alert-signup-card">
            <div class="alert-content">
                <h2>🚨 Never Miss a Deal</h2>
                <p>Get instant notifications when we find amazing deals on your favorite tech products.</p>
            </div>
            <form class="deal-alert-form" data-action="newsletter/subscribe" method="post">
                <input type="email" 
                       name="email" 
                       placeholder="Enter your email for deal alerts"
                       required>
                <input type="hidden" name="source" value="deal_alerts">
                <input type="hidden" name="language" value="<?= $current_lang ?>">
                <button type="submit" class="btn btn-primary">Get Deal Alerts</button>
            </form>
        </div>
    </div>
</section>

<!-- Deals Categories Filter -->
<section class="deals-filters">
    <div class="container">
        <div class="filter-tabs">
            <button class="filter-tab active" data-category="all">All Deals</button>
            <button class="filter-tab" data-category="audio">🎧 Audio</button>
            <button class="filter-tab" data-category="monitors">🖥️ Monitors</button>
            <button class="filter-tab" data-category="keyboards">⌨️ Input Devices</button>
            <button class="filter-tab" data-category="webcams">📹 Webcams</button>
            <button class="filter-tab" data-category="desks">🪑 Furniture</button>
            <button class="filter-tab" data-category="accessories">⚡ Accessories</button>
        </div>
        
        <div class="deals-sort">
            <select id="deals-sort">
                <option value="savings">Best Savings</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="ending">Ending Soon</option>
                <option value="newest">Newly Added</option>
            </select>
        </div>
    </div>
</section>

<!-- Hot Deals Section -->
<section class="hot-deals">
    <div class="container">
        <h2>🔥 Hot Deals - Limited Time</h2>
        <div class="hot-deals-grid">
            <!-- Example Hot Deal 1 -->
            <div class="hot-deal-card">
                <div class="deal-badge hot">HOT</div>
                <div class="deal-timer" data-expires="2025-09-20T23:59:59">
                    <div class="timer-label">Ends in:</div>
                    <div class="timer-display">
                        <span class="hours">12</span>h <span class="minutes">34</span>m
                    </div>
                </div>
                <div class="deal-image">
                    <img src="<?= asset('images/deals/sony-headphones.jpg') ?>" 
                         alt="Sony WH-1000XM5 Headphones"
                         width="300" height="200"
                         loading="lazy">
                </div>
                <div class="deal-content">
                    <h3>Sony WH-1000XM5 Wireless Headphones</h3>
                    <div class="deal-price">
                        <span class="original-price">$399.99</span>
                        <span class="deal-price-current">$279.99</span>
                        <span class="discount-percent">-30%</span>
                    </div>
                    <div class="deal-savings">Save $120.00</div>
                    <a href="#" class="btn btn-primary btn-deal" target="_blank" rel="nofollow">
                        Get Deal →
                    </a>
                </div>
            </div>
            
            <!-- Example Hot Deal 2 -->
            <div class="hot-deal-card">
                <div class="deal-badge limited">LIMITED</div>
                <div class="deal-timer" data-expires="2025-09-18T15:30:00">
                    <div class="timer-label">Flash Sale:</div>
                    <div class="timer-display">
                        <span class="hours">08</span>h <span class="minutes">15</span>m
                    </div>
                </div>
                <div class="deal-image">
                    <img src="<?= asset('images/deals/lg-monitor.jpg') ?>" 
                         alt="LG 27-inch 4K Monitor"
                         width="300" height="200"
                         loading="lazy">
                </div>
                <div class="deal-content">
                    <h3>LG 27" 4K UltraFine Monitor</h3>
                    <div class="deal-price">
                        <span class="original-price">$699.99</span>
                        <span class="deal-price-current">$449.99</span>
                        <span class="discount-percent">-36%</span>
                    </div>
                    <div class="deal-savings">Save $250.00</div>
                    <a href="#" class="btn btn-primary btn-deal" target="_blank" rel="nofollow">
                        Get Deal →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- All Deals Section -->
<section class="all-deals">
    <div class="container">
        <h2>All Current Deals</h2>
        
        <?php if (!empty($deals)): ?>
        <div class="deals-grid" id="deals-grid">
            <?php foreach ($deals as $deal): ?>
            <div class="deal-card filterable-item" data-category="<?= $deal['category'] ?>">
                <?php if ($deal['discount_percentage'] >= 50): ?>
                <div class="deal-badge mega">MEGA DEAL</div>
                <?php elseif ($deal['discount_percentage'] >= 30): ?>
                <div class="deal-badge hot">HOT</div>
                <?php endif; ?>
                
                <div class="deal-image">
                    <img src="<?= $deal['featured_image'] ?>" 
                         alt="<?= clean($deal['title']) ?>"
                         width="280" height="200"
                         loading="lazy">
                    
                    <!-- Deal overlay info -->
                    <div class="deal-overlay">
                        <div class="discount-big">
                            -<?= $deal['discount_percentage'] ?>%
                        </div>
                    </div>
                </div>
                
                <div class="deal-content">
                    <div class="deal-category"><?= ucfirst($deal['category']) ?></div>
                    
                    <h3 class="deal-title">
                        <a href="<?= url('reviews/' . $deal['slug']) ?>">
                            <?= clean($deal['title']) ?>
                        </a>
                    </h3>
                    
                    <div class="deal-rating">
                        <div class="stars" data-rating="<?= $deal['rating'] ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $deal['rating'] ? 'filled' : '' ?>">★</span>
        <?php foreach; ?>
                        </div>
                        <span class="rating-text"><?= $deal['rating'] ?>/5</span>
                    </div>
                    
                    <div class="deal-price">
                        <div class="price-row">
                            <span class="original-price">$<?= number_format($deal['price'], 2) ?></span>
                            <span class="deal-price-current">$<?= number_format($deal['discount_price'], 2) ?></span>
                        </div>
                        <div class="savings-row">
                            <span class="discount-percent">-<?= $deal['discount_percentage'] ?>%</span>
                            <span class="savings-amount">Save $<?= number_format($deal['price'] - $deal['discount_price'], 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="deal-actions">
                        <a href="<?= url('reviews/' . $deal['slug']) ?>" class="btn btn-outline btn-sm">
                            Read Review
                        </a>
                        <a href="<?= $deal['affiliate_link'] ?>" 
                           class="btn btn-primary btn-sm btn-deal" 
                           target="_blank" 
                           rel="nofollow noopener"
                           data-deal-id="<?= $deal['id'] ?>">
                            Get Deal →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        
        <?php else: ?>
        <!-- No deals state -->
        <div class="no-deals-state">
            <div class="no-deals-content">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                <h3>No Active Deals Right Now</h3>
                <p>We're constantly hunting for the best deals. Check back soon or subscribe to get notified!</p>
                <a href="#deal-alerts" class="btn btn-primary">Get Deal Alerts</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Deal Tips Section -->
<section class="deal-tips">
    <div class="container">
        <div class="deal-tips-grid">
            <div class="tip-card">
                <div class="tip-icon">⏰</div>
                <h3>Act Fast</h3>
                <p>The best deals often have limited quantities or time limits. Don't wait too long!</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">📊</div>
                <h3>Compare Prices</h3>
                <p>We verify all prices, but it's always good to double-check before purchasing.</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">✅</div>
                <h3>Read Reviews</h3>
                <p>Check our detailed reviews before buying to make sure the product fits your needs.</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">🔔</div>
                <h3>Get Alerts</h3>
                <p>Subscribe to deal alerts so you never miss out on savings for your favorite products.</p>
            </div>
        </div>
    </div>
</section>

<!-- Affiliate Disclaimer -->
<section class="affiliate-disclaimer">
    <div class="container">
        <div class="disclaimer-content">
            <h3>💡 Transparency Notice</h3>
            <p>We may earn a small commission when you purchase through our affiliate links, at no extra cost to you. This helps us keep the site running and find more great deals. We only recommend products we genuinely believe in and have thoroughly tested.</p>
        </div>
    </div>
</section>

<style>
.deals-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: var(--space-16) 0;
    text-align: center;
}

.deals-header h1 {
    font-size: var(--font-size-5xl);
    margin-bottom: var(--space-4);
}

.deals-stats {
    display: flex;
    justify-content: center;
    gap: var(--space-12);
    margin-top: var(--space-8);
}

.deal-stat {
    text-align: center;
}

.deal-stat .stat-number {
    font-size: var(--font-size-3xl);
    font-weight: 700;
    margin-bottom: var(--space-2);
}

.deal-stat .stat-label {
    font-size: var(--font-size-sm);
    opacity: 0.9;
}

.deal-alerts {
    padding: var(--space-12) 0;
    background: var(--gray-50);
}

.alert-signup-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: var(--space-8);
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--box-shadow);
}

.deal-alert-form {
    display: flex;
    gap: var(--space-4);
    min-width: 400px;
}

.deal-alert-form input {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
}

.deals-filters {
    padding: var(--space-8) 0;
    background: white;
    border-bottom: 1px solid var(--gray-200);
}

.deals-filters .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filter-tabs {
    display: flex;
    gap: var(--space-4);
    overflow-x: auto;
}

.filter-tab {
    padding: var(--space-3) var(--space-6);
    border: none;
    background: var(--gray-100);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}

.filter-tab.active,
.filter-tab:hover {
    background: var(--primary);
    color: white;
}

#deals-sort {
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    background: white;
}

.hot-deals {
    padding: var(--space-16) 0;
    background: var(--gray-50);
}

.hot-deals h2 {
    text-align: center;
    margin-bottom: var(--space-12);
    font-size: var(--font-size-3xl);
}

.hot-deals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: var(--space-8);
}

.hot-deal-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow-lg);
    position: relative;
    border: 2px solid #ff6b6b;
}

.deal-badge {
    position: absolute;
    top: var(--space-4);
    left: var(--space-4);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 700;
    z-index: 2;
}

.deal-badge.hot {
    background: #ff6b6b;
    color: white;
}

.deal-badge.limited {
    background: #ff9500;
    color: white;
}

.deal-badge.mega {
    background: #ff1744;
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.deal-timer {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    text-align: center;
    z-index: 2;
}

.timer-display {
    font-weight: 700;
    margin-top: var(--space-1);
}

.deal-image {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
}

.deal-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.deal-overlay {
    position: absolute;
    bottom: var(--space-4);
    right: var(--space-4);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--border-radius);
}

.discount-big {
    font-size: var(--font-size-lg);
    font-weight: 700;
}

.deal-content {
    padding: var(--space-6);
}

.deal-category {
    font-size: var(--font-size-sm);
    color: var(--primary);
    font-weight: 600;
    margin-bottom: var(--space-2);
}

.deal-title {
    margin-bottom: var(--space-4);
}

.deal-title a {
    color: var(--gray-900);
    text-decoration: none;
}

.deal-title a:hover {
    color: var(--primary);
}

.deal-rating {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}

.stars {
    display: flex;
    gap: 1px;
}

.star {
    color: var(--gray-300);
}

.star.filled {
    color: #fbbf24;
}

.deal-price {
    margin-bottom: var(--space-6);
}

.price-row {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-2);
}

.original-price {
    text-decoration: line-through;
    color: var(--gray-500);
    font-size: var(--font-size-sm);
}

.deal-price-current {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--success);
}

.savings-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.discount-percent {
    background: var(--error);
    color: white;
    padding: var(--space-1) var(--space-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.savings-amount {
    color: var(--success);
    font-weight: 600;
    font-size: var(--font-size-sm);
}

.deal-actions {
    display: flex;
    gap: var(--space-3);
}

.btn-deal {
    position: relative;
    overflow: hidden;
}

.btn-deal::after {
    content: '🎯';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn-deal:hover::after {
    opacity: 1;
}

.all-deals {
    padding: var(--space-16) 0;
}

.deals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-8);
}

.deal-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition-slow);
    position: relative;
}

.deal-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--box-shadow-lg);
}

.no-deals-state {
    text-align: center;
    padding: var(--space-20) 0;
}

.no-deals-content svg {
    color: var(--gray-400);
    margin-bottom: var(--space-6);
}

.deal-tips {
    padding: var(--space-16) 0;
    background: var(--gray-50);
}

.deal-tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
}

.tip-card {
    background: white;
    padding: var(--space-6);
    border-radius: var(--border-radius-lg);
    text-align: center;
    box-shadow: var(--box-shadow);
}

.tip-icon {
    font-size: 3rem;
    margin-bottom: var(--space-4);
}

.affiliate-disclaimer {
    background: var(--gray-900);
    color: var(--gray-300);
    padding: var(--space-12) 0;
    text-align: center;
}

.disclaimer-content {
    max-width: 800px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .alert-signup-card {
        flex-direction: column;
        gap: var(--space-6);
        text-align: center;
    }
    
    .deal-alert-form {
        min-width: auto;
        width: 100%;
    }
    
    .deals-filters .container {
        flex-direction: column;
        gap: var(--space-4);
    }
    
    .deals-stats {
        flex-direction: column;
        gap: var(--space-6);
    }
    
    .hot-deals-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Deal timers
function updateDealTimers() {
    document.querySelectorAll('.deal-timer[data-expires]').forEach(timer => {
        const expiryDate = new Date(timer.dataset.expires);
        const now = new Date();
        const diff = expiryDate - now;
        
        if (diff > 0) {
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            const hoursSpan = timer.querySelector('.hours');
            const minutesSpan = timer.querySelector('.minutes');
            
            if (hoursSpan) hoursSpan.textContent = hours.toString().padStart(2, '0');
            if (minutesSpan) minutesSpan.textContent = minutes.toString().padStart(2, '0');
        } else {
            timer.innerHTML = '<div class="timer-label">Expired</div>';
        }
    });
}

// Update timers every minute
setInterval(updateDealTimers, 60000);
updateDealTimers(); // Initial call

// Category filtering
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        // Filter deals
        const category = tab.dataset.category;
        const deals = document.querySelectorAll('.filterable-item');
        
        deals.forEach(deal => {
            if (category === 'all' || deal.dataset.category === category) {
                deal.style.display = 'block';
            } else {
                deal.style.display = 'none';
            }
        });
    });
});

// Deal click tracking
document.querySelectorAll('.btn-deal').forEach(button => {
    button.addEventListener('click', (e) => {
        const dealId = button.dataset.dealId;
        
        // Track deal click
        if (window.gtag && dealId) {
            gtag('event', 'deal_click', {
                event_category: 'deals',
                event_label: dealId,
                value: 1
            });
        }
        
        // Visual feedback
        button.style.background = '#10b981';
        button.innerHTML = '✅ Opening...';
        
        setTimeout(() => {
            button.innerHTML = 'Get Deal →';
            button.style.background = '';
        }, 3000);
    });
});

// Sort functionality
document.getElementById('deals-sort')?.addEventListener('change', function() {
    const sortBy = this.value;
    const grid = document.getElementById('deals-grid');
    const deals = Array.from(grid.children);
    
    deals.sort((a, b) => {
        switch (sortBy) {
            case 'savings':
                const savingsA = parseFloat(a.querySelector('.savings-amount').textContent.replace(/[^0-9.]/g, ''));
                const savingsB = parseFloat(b.querySelector('.savings-amount').textContent.replace(/[^0-9.]/g, ''));
                return savingsB - savingsA;
            case 'price-low':
                const priceA = parseFloat(a.querySelector('.deal-price-current').textContent.replace(/[^0-9.]/g, ''));
                const priceB = parseFloat(b.querySelector('.deal-price-current').textContent.replace(/[^0-9.]/g, ''));
                return priceA - priceB;
            case 'price-high':
                const priceHighA = parseFloat(a.querySelector('.deal-price-current').textContent.replace(/[^0-9.]/g, ''));
                const priceHighB = parseFloat(b.querySelector('.deal-price-current').textContent.replace(/[^0-9.]/g, ''));
                return priceHighB - priceHighA;
            default:
                return 0;
        }
    });
    
    // Re-append sorted elements
    deals.forEach(deal => grid.appendChild(deal));
});
</script>