<div class="consultation-modal" id="consultation-modal" aria-hidden="true">
    <div class="consultation-backdrop" data-consultation-close></div>
    <section class="consultation-dialog" role="dialog" aria-modal="true" aria-labelledby="consultation-title">
        <button class="consultation-close" type="button" data-consultation-close aria-label="Close consultation form"><span></span><span></span></button>
        <div class="consultation-intro">
            <span class="consultation-kicker">Let’s build lasting impact</span>
            <h2 id="consultation-title">Book a consultation</h2>
            <p>Tell us what you are working on. Our advisory team will review your requirement and get back to you.</p>
            <div class="consultation-promise"><b>✓</b><span><strong>Expert-led conversation</strong><small>Practical guidance from sector specialists</small></span></div>
            <div class="consultation-promise"><b>✓</b><span><strong>Confidential by design</strong><small>Your information stays with our advisory team</small></span></div>
            <div class="consultation-promise"><b>✓</b><span><strong>Focused response</strong><small>We route every request to the right expert</small></span></div>
        </div>
        <form class="consultation-form" action="<?= htmlspecialchars(url('/consultation/request')) ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <input class="consultation-honeypot" type="text" name="website" tabindex="-1" autocomplete="off">
            <div class="consultation-grid">
                <label><span>Full name *</span><i>◎</i><input name="name" type="text" autocomplete="name" required minlength="2" maxlength="100" placeholder="Your full name"></label>
                <label><span>Work email *</span><i>✉</i><input name="email" type="email" autocomplete="email" required maxlength="190" placeholder="you@company.com"></label>
                <label><span>Phone number *</span><i>⌕</i><input name="phone" type="tel" autocomplete="tel" required minlength="7" maxlength="25" placeholder="+91 98765 43210"></label>
                <label><span>Organization</span><i>▦</i><input name="organization" type="text" autocomplete="organization" maxlength="150" placeholder="Company or institution"></label>
                <label><span>Service of interest</span><i>◇</i><select name="service"><option value="">Select a service</option><option>Project Advisory</option><option>Funding & Resource Mobilization</option><option>Institutional Development</option><option>Research & Insights</option><option>Digital Transformation</option><option>Training & Capacity Building</option></select></label>
                <label><span>Indicative budget</span><i>₹</i><select name="budget"><option value="">Select a range</option><option>Below ₹5 lakh</option><option>₹5–15 lakh</option><option>₹15–50 lakh</option><option>₹50 lakh–₹1 crore</option><option>Above ₹1 crore</option><option>To be discussed</option></select></label>
            </div>
            <label class="consultation-message"><span>How can we help? *</span><i>✎</i><textarea name="message" required minlength="10" maxlength="3000" rows="4" placeholder="Share your objective, challenges, timelines, and the outcomes you want to create."></textarea></label>
            <fieldset><legend>Preferred way to connect</legend><label><input type="radio" name="preferred_contact" value="Email" checked><span>Email</span></label><label><input type="radio" name="preferred_contact" value="Phone"><span>Phone</span></label><label><input type="radio" name="preferred_contact" value="Video call"><span>Video call</span></label></fieldset>
            <div class="consultation-status" role="status" aria-live="polite"></div>
            <button class="consultation-submit" type="submit"><span>Send consultation request</span><b>→</b></button>
            <small class="consultation-privacy">By submitting, you agree that Udyam Ventures may contact you regarding this request.</small>
        </form>
    </section>
</div>
<script>
(() => {
    const modal = document.getElementById('consultation-modal');
    if (!modal || modal.dataset.ready === 'true') return;
    modal.dataset.ready = 'true';
    const form = modal.querySelector('.consultation-form');
    const status = modal.querySelector('.consultation-status');
    const submit = modal.querySelector('.consultation-submit');
    let returnFocus = null;
    const setModal = open => {
        modal.classList.toggle('is-open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('consultation-open', open);
        if (open) {
            returnFocus = document.activeElement;
            setTimeout(() => form.querySelector('input[name="name"]')?.focus(), 180);
        } else {
            returnFocus?.focus?.();
        }
    };
    document.querySelectorAll('.site-cta,.mobile-consultation').forEach(button => button.addEventListener('click', event => {
        event.preventDefault();
        document.body.classList.remove('mobile-menu-open');
        document.querySelector('.mobile-navigation')?.classList.remove('is-open');
        document.querySelector('.mobile-menu-overlay')?.classList.remove('is-open');
        setModal(true);
    }));
    modal.querySelectorAll('[data-consultation-close]').forEach(button => button.addEventListener('click', () => setModal(false)));
    window.addEventListener('keydown', event => { if (event.key === 'Escape' && modal.classList.contains('is-open')) setModal(false); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        status.className = 'consultation-status';
        if (!form.reportValidity()) return;
        submit.disabled = true;
        submit.classList.add('is-loading');
        status.textContent = 'Securely sending your request…';
        try {
            const response = await fetch(form.action, {method:'POST', body:new FormData(form), headers:{Accept:'application/json'}});
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'Unable to submit your request.');
            status.classList.add('is-success');
            status.textContent = data.message;
            form.reset();
            setTimeout(() => setModal(false), 2400);
        } catch (error) {
            status.classList.add('is-error');
            status.textContent = error.message || 'Something went wrong. Please try again.';
        } finally {
            submit.disabled = false;
            submit.classList.remove('is-loading');
        }
    });
})();
</script>
