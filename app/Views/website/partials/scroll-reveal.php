<script>
(() => {
    if (document.documentElement.dataset.revealReady === 'true') return;
    document.documentElement.dataset.revealReady = 'true';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const sections = [...document.querySelectorAll('main > section, body > section, .hero-stage, .site-footer')];
    const childSelector = [
        '.section-heading > *', '.hero-copy > *', '.hero-capability-card',
        '.audience-grid > *', '.impact-strip > *', '.tender-intro > *',
        '.tender-heading > *', '.tender-tabs > *', '.tender-table tbody tr',
        '.journey-card > *', '.journey-row > *', '.why-card > *',
        '.service-heading > *', '.service-grid > *', '.focus-ecosystem-section > *',
        '.premium-section-heading > *', '.statement-grid > *', '.expertise-grid > *',
        '.office-grid > *', '.contact-channel-panel > *', '.contact-channel-list > *',
        '.partners > *', '.network-grid > *', '.insight-grid > *',
        '.content-block > *', '.feature-grid > *', '.page-stat-strip > *',
        '.faq-section details', '.closing-cta > *', '.footer-main > *', '.footer-bottom > *'
    ].join(',');

    const targets = [];
    sections.forEach((section, sectionIndex) => {
        const children = [...section.querySelectorAll(childSelector)]
            .filter(element => !element.parentElement?.closest('.scroll-reveal'));
        const revealTargets = children.length ? children : [section];
        revealTargets.forEach((element, index) => {
            if (element.closest('.consultation-modal,.mobile-navigation')) return;
            element.classList.add('scroll-reveal');
            element.style.setProperty('--reveal-delay', `${Math.min(index, 7) * 55}ms`);
            element.dataset.revealDirection = index % 3 === 1 ? 'right' : index % 3 === 2 ? 'left' : 'up';
            targets.push(element);
        });
    });

    document.documentElement.classList.add('reveal-ready');
    if (reducedMotion || typeof window.IntersectionObserver !== 'function') {
        targets.forEach(element => element.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, {threshold: 0.12, rootMargin: '0px 0px -7% 0px'});

    targets.forEach(element => observer.observe(element));
    window.setTimeout(() => {
        targets.forEach(element => {
            if (element.getBoundingClientRect().top < window.innerHeight * 1.15) {
                element.classList.add('is-revealed');
                observer.unobserve(element);
            }
        });
    }, 700);
})();
</script>
