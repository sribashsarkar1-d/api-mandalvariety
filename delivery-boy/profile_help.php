<?php
require_once 'includes/config.php';
checkDeliveryLogin();
?>
<?php include 'includes/header.php'; ?>

<style>
    body { background-color: #f8fafc; }
    .page-container { padding: 24px 20px; padding-bottom: 100px; }
    .page-header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
    .back-btn { color: var(--text-dark); text-decoration: none; font-size: 1.2rem; }
    .page-title { font-weight: 800; font-size: 1.25rem; margin: 0; color: var(--text-dark); }
    
    .support-hero { text-align: center; margin-bottom: 30px; }
    .support-hero i { font-size: 4rem; color: var(--mandal-green); margin-bottom: 15px; opacity: 0.9; }
    .support-hero h2 { font-weight: 800; font-size: 1.5rem; color: var(--text-dark); margin-bottom: 5px; }
    .support-hero p { color: var(--text-muted); font-size: 0.9rem; }

    .contact-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); margin-bottom: 24px; display: flex; align-items: center; gap: 15px; text-decoration: none; transition: 0.2s; }
    .contact-card:active { transform: scale(0.98); }
    .contact-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    
    .contact-text { flex: 1; }
    .contact-title { font-weight: 700; color: var(--text-dark); font-size: 1rem; margin-bottom: 2px; }
    .contact-desc { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

    .faq-section { margin-top: 30px; }
    .faq-title { font-weight: 800; font-size: 1.1rem; color: var(--text-dark); margin-bottom: 15px; }
    
    .faq-item { background: white; border-radius: 16px; padding: 16px 20px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02); }
    .faq-q { font-weight: 700; font-size: 0.95rem; color: var(--text-dark); margin-bottom: 8px; }
    .faq-a { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }
</style>

<div class="page-container">
    <div class="page-header">
        <a href="profile.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">Help & Support</h4>
    </div>

    <div class="support-hero">
        <i class="fa-solid fa-headset"></i>
        <h2>How can we help?</h2>
        <p>Our support team is always here for you</p>
    </div>

    <a href="tel:+919876543210" class="contact-card">
        <div class="contact-icon" style="background: #eef2ff; color: #4f46e5;">
            <i class="fa-solid fa-phone"></i>
        </div>
        <div class="contact-text">
            <div class="contact-title">Call Admin Helpdesk</div>
            <div class="contact-desc">+91 98765 43210</div>
        </div>
        <i class="fa-solid fa-chevron-right text-muted"></i>
    </a>

    <a href="mailto:support@mandalvariety.com" class="contact-card">
        <div class="contact-icon" style="background: #fdf4ff; color: #d946ef;">
            <i class="fa-solid fa-envelope"></i>
        </div>
        <div class="contact-text">
            <div class="contact-title">Email Support</div>
            <div class="contact-desc">support@mandalvariety.com</div>
        </div>
        <i class="fa-solid fa-chevron-right text-muted"></i>
    </a>

    <div class="faq-section">
        <div class="faq-title">Frequently Asked Questions</div>
        
        <div class="faq-item">
            <div class="faq-q">What if the customer is unreachable?</div>
            <div class="faq-a">Wait at the location for 10 minutes and try calling 3 times. If still unreachable, mark the order as failed or contact the admin.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-q">How is my COD collected?</div>
            <div class="faq-a">You must deposit the collected Cash on Delivery (COD) amount to the store admin at the end of your daily shift.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-q">Can I reject an assigned order?</div>
            <div class="faq-a">Yes, you can reject an order from the 'New Delivery Request' screen if you are unable to fulfill it. It will be reassigned.</div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
