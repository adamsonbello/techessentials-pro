<?php
/**
 * TechEssentials Pro - Template Contact
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
<section class="contact-header">
    <div class="container">
        <div class="contact-header-content">
            <h1><?= Language::get('contact.title') ?></h1>
            <p><?= Language::get('contact.description') ?></p>
        </div>
        
        <!-- Contact Info Cards -->
        <div class="contact-info-grid">
            <div class="contact-info-card">
                <div class="contact-info-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3>Email Us</h3>
                <p>hello@techessentialspro.com</p>
            </div>
            
            <div class="contact-info-card">
                <div class="contact-info-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Response Time</h3>
                <p>Within 24 hours</p>
            </div>
            
            <div class="contact-info-card">
                <div class="contact-info-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3>Based in</h3>
                <p>Paris, France</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="contact-form-section">
    <div class="container">
        <div class="contact-content-grid">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <div class="contact-form-header">
                    <h2>Send us a Message</h2>
                    <p>Have a question, suggestion, or want to request a product review? We'd love to hear from you!</p>
                </div>
                
                <?php if (isset($form_result)): ?>
                <?php if ($form_result['success']): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?= $form_result['message'] ?></span>
                </div>
                <?php else: ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?= $form_result['error'] ?? 'Something went wrong. Please try again.' ?></span>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <form class="contact-form" method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <?= Language::get('contact.form.name') ?> *
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-input" 
                                   value="<?= clean($_POST['name'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <?= Language::get('contact.form.email') ?> *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input" 
                                   value="<?= clean($_POST['email'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">
                            <?= Language::get('contact.form.subject') ?>
                        </label>
                        <select id="subject" name="subject" class="form-input">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Product Review Request">Product Review Request</option>
                            <option value="Partnership Proposal">Partnership Proposal</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Press Inquiry">Press Inquiry</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">
                            <?= Language::get('contact.form.message') ?> *
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  class="form-input" 
                                  rows="6" 
                                  placeholder="Tell us more about your inquiry..."
                                  required><?= clean($_POST['message'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Honeypot pour anti-spam -->
                    <div style="position: absolute; left: -9999px;">
                        <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg form-submit">
                        <span class="btn-text"><?= Language::get('contact.form.send_button') ?></span>
                        <svg class="btn-spinner" width="20" height="20" viewBox="0 0 50 50">
                            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4" stroke-miterlimit="10" stroke-dasharray="157" stroke-dashoffset="157" stroke-linecap="round"></circle>
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- FAQ Sidebar -->
            <div class="contact-sidebar">
                <div class="faq-section">
                    <h3>Frequently Asked Questions</h3>
                    
                    <div class="faq-item">
                        <button class="faq-question" type="button">
                            <span>How do you select products to review?</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>We select products based on community requests, market trends, and our expertise in remote work tools. We prioritize products that can genuinely improve productivity and workspace quality.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" type="button">
                            <span>Do you accept free products for review?</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Yes, but acceptance doesn't guarantee a positive review. We maintain editorial independence and always disclose when products are provided for review. Our opinions remain unbiased.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" type="button">
                            <span>How long does it take to publish a review?</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Our thorough review process typically takes 2-4 weeks. This includes extensive testing, research, and writing to ensure we provide valuable insights.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" type="button">
                            <span>Can I suggest a product for review?</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Absolutely! We love community suggestions. Use the contact form above with "Product Review Request" as the subject and tell us what you'd like us to review.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" type="button">
                            <span>Do you offer sponsored content?</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>We occasionally work with brands on sponsored content, but it's always clearly labeled. Sponsored posts meet the same quality standards as our regular content.</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-cta">
                    <div class="contact-cta-content">
                        <h4>Prefer Email?</h4>
                        <p>Send us a direct message at:</p>
                        <a href="mailto:hello@techessentialspro.com" class="email-link">
                            hello@techessentialspro.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof Section -->
<section class="social-proof-section">
    <div class="container">
        <div class="social-proof-content">
            <h2>Trusted by the Community</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"TechEssentials Pro helped me build the perfect home office setup. Their reviews are thorough and honest."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-info">
                            <strong>Sarah Chen</strong>
                            <span>Product Designer</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"I've saved hundreds of dollars thanks to their deal alerts and honest product recommendations."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-info">
                            <strong>Mike Rodriguez</strong>
                            <span>Software Engineer</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The most reliable source for remote work tech. I trust their recommendations completely."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-info">
                            <strong>Emma Thompson</strong>
                            <span>Marketing Manager</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Schema.org -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ContactPage",
    "name": "<?= Language::get('meta.contact.title') ?>",
    "description": "<?= Language::get('meta.contact.description') ?>",
    "url": "<?= url('contact') ?>",
    "mainEntity": {
        "@type": "Organization",
        "name": "TechEssentials Pro",
        "email": "hello@techessentialspro.com",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Paris",
            "addressCountry": "FR"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "email": "hello@techessentialspro.com",
            "availableLanguage": ["English", "French"]
        }
    }
}
</script>

<style>
.contact-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: var(--space-16) 0;
    text-align: center;
}

.contact-header-content {
    margin-bottom: var(--space-12);
}

.contact-header h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--space-4);
}

.contact-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
    max-width: 800px;
    margin: 0 auto;
}

.contact-info-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius-lg);
    padding: var(--space-6);
    text-align: center;
    backdrop-filter: blur(10px);
}

.contact-info-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-4);
}

.contact-form-section {
    padding: var(--space-20) 0;
}

.contact-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-16);
    align-items: start;
}

.contact-form-container {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: var(--space-8);
    box-shadow: var(--box-shadow-lg);
}

.contact-form-header {
    margin-bottom: var(--space-8);
    text-align: center;
}

.alert {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    border-radius: var(--border-radius);
    margin-bottom: var(--space-6);
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-6);
}

.form-group {
    margin-bottom: var(--space-6);
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: var(--space-2);
    color: var(--gray-700);
}

.form-input {
    width: 100%;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: var(--font-size-base);
    transition: var(--transition);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-submit {
    width: 100%;
    position: relative;
}

.btn-spinner {
    display: none;
    animation: spin 1s linear infinite;
}

.form-submit.loading .btn-text {
    opacity: 0;
}

.form-submit.loading .btn-spinner {
    display: block;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.contact-sidebar {
    background: var(--gray-50);
    border-radius: var(--border-radius-lg);
    padding: var(--space-8);
}

.faq-section {
    margin-bottom: var(--space-8);
}

.faq-item {
    border-bottom: 1px solid var(--gray-200);
    margin-bottom: var(--space-4);
}

.faq-question {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) 0;
    background: none;
    border: none;
    text-align: left;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.faq-question:hover {
    color: var(--primary);
}

.faq-question svg {
    transition: var(--transition);
}

.faq-item.active .faq-question svg {
    transform: rotate(180deg);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}

.faq-item.active .faq-answer {
    max-height: 200px;
}

.faq-answer p {
    padding-bottom: var(--space-4);
    color: var(--gray-600);
    line-height: 1.6;
}

.contact-cta {
    background: white;
    border-radius: var(--border-radius);
    padding: var(--space-6);
    text-align: center;
}

.email-link {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
}

.email-link:hover {
    text-decoration: underline;
}

.social-proof-section {
    background: var(--gray-50);
    padding: var(--space-20) 0;
}

.social-proof-content {
    text-align: center;
}

.social-proof-content h2 {
    margin-bottom: var(--space-12);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-8);
}

.testimonial-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: var(--space-6);
    box-shadow: var(--box-shadow);
}

.testimonial-content {
    margin-bottom: var(--space-4);
    font-style: italic;
    color: var(--gray-600);
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.author-info strong {
    display: block;
    font-weight: 600;
    color: var(--gray-900);
}

.author-info span {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
}

@media (max-width: 768px) {
    .contact-content-grid {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .contact-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// FAQ Accordion
document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const faqItem = button.closest('.faq-item');
        const isActive = faqItem.classList.contains('active');
        
        // Fermer tous les autres
        document.querySelectorAll('.faq-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Toggle celui-ci
        if (!isActive) {
            faqItem.classList.add('active');
        }
    });
});

// Form submission handling
const form = document.querySelector('.contact-form');
const submitBtn = form.querySelector('.form-submit');

form.addEventListener('submit', () => {
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});
</script>