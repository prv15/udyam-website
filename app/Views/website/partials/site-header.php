<?php $header = is_array($header ?? null) ? $header : []; ?>
<?php
// The portal button label is admin-editable content, but older/unmigrated
// data may still say "Customer Portal" (or "Client Portal"). Normalize it
// here so the site always shows "Partner Portal" without requiring a
// manual database edit; admins can still override it to something else
// entirely via the CMS and that custom value will be respected.
$portalLabel = trim((string) ($header['portal_label'] ?? ''));
if ($portalLabel === '' || in_array($portalLabel, ['Customer Portal', 'Client Portal'], true)) {
    $portalLabel = 'Partner Portal';
}
?>
<header class="site-header">
    <a class="site-brand" href="<?= htmlspecialchars(url('/')) ?>">
        <?php if (!empty($header['logo_url'])): ?><img src="<?= htmlspecialchars($header['logo_url']) ?>" alt="Udyam Ventures">
        <?php else: ?><img src="<?= htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures"><?php endif; ?>
    </a>
    <nav class="desktop-navigation">
        <?php $resourceRendered = false; ?>
        <?php foreach (($header['navigation'] ?? []) as $item): ?>
            <?php
            $navLabel = strtolower(trim((string) ($item['label'] ?? '')));
            if (in_array($navLabel, ['knowledge centre', 'knowledge center', 'notice & tender', 'notice & tenders'], true)) continue;
            ?>
            <?php if ($navLabel === 'contact' && !empty($header['resource_links'])): $resourceRendered = true; ?>
                <?php require __DIR__ . '/resource-menu.php'; ?>
            <?php endif; ?>
            <a href="<?= htmlspecialchars(url($item['url'] ?? '#')) ?>"><?= htmlspecialchars($item['label'] ?? '') ?></a>
            <?php if ($navLabel === 'services'): ?><a href="<?= htmlspecialchars(url('/subscription-plans')) ?>">Subscription Plans</a><?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$resourceRendered && !empty($header['resource_links'])) require __DIR__ . '/resource-menu.php'; ?>
    </nav>
    <div class="header-actions">
        <?php $headerCustomer = \App\Core\Session::get('customer_user'); ?>
        <?php if (is_array($headerCustomer) && !empty($headerCustomer['id'])): ?>
            <?php
                $headerInitials = strtoupper(substr((string)($headerCustomer['first_name'] ?? ''), 0, 1) . substr((string)($headerCustomer['last_name'] ?? ''), 0, 1));
                if ($headerInitials === '') $headerInitials = 'U';
                $headerFullName = trim(($headerCustomer['first_name'] ?? '') . ' ' . ($headerCustomer['last_name'] ?? ''));
                if ($headerFullName === '') $headerFullName = 'My Account';
            ?>
            <div class="header-account">
                <button type="button" class="header-account-trigger" aria-haspopup="true" aria-expanded="false">
                    <span class="header-account-avatar"><?= htmlspecialchars($headerInitials) ?></span>
                    <span class="header-account-text"><strong><?= htmlspecialchars($headerFullName) ?></strong><small>Welcome Back</small></span>
                    <span class="header-account-chevron">⌄</span>
                </button>
                <div class="header-account-menu">
                    <a href="<?= htmlspecialchars(url('/customer/dashboard')) ?>">Dashboard</a>
                    <form method="post" action="<?= htmlspecialchars(url('/customer/logout')) ?>"><?= csrf_field() ?><button type="submit">Logout</button></form>
                </div>
            </div>
        <?php else: ?>
            <a class="portal-cta" href="<?= htmlspecialchars(url($header['portal_url'] ?? '/customer/login')) ?>"><?= htmlspecialchars($portalLabel) ?></a>
        <?php endif; ?>
        <a class="site-cta" href="<?= htmlspecialchars(url($header['consultation_url'] ?? '/contact')) ?>"><?= htmlspecialchars($header['consultation_label'] ?? 'Book Consultation') ?> <span>→</span></a>
    </div>
    <button class="mobile-menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="mobile-navigation" aria-expanded="false"><span></span><span></span><span></span></button>
</header>
<div class="mobile-menu-overlay" aria-hidden="true"></div>
<aside class="mobile-navigation" id="mobile-navigation" aria-hidden="true">
    <div class="mobile-menu-head">
        <a class="mobile-menu-brand" href="<?= htmlspecialchars(url('/')) ?>"><?php if (!empty($header['logo_url'])): ?><img src="<?= htmlspecialchars($header['logo_url']) ?>" alt="Udyam Ventures"><?php else: ?><img src="<?= htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures"><?php endif; ?></a>
        <button class="mobile-menu-close" type="button" aria-label="Close navigation menu"><span></span><span></span></button>
    </div>
    <nav aria-label="Mobile navigation">
        <?php foreach (($header['navigation'] ?? []) as $item): ?>
            <?php $mobileLabel = strtolower(trim((string) ($item['label'] ?? ''))); ?>
            <?php if (in_array($mobileLabel, ['knowledge centre', 'knowledge center', 'notice & tender', 'notice & tenders'], true)) continue; ?>
            <a href="<?= htmlspecialchars(url($item['url'] ?? '#')) ?>"><span><?= htmlspecialchars($item['label'] ?? '') ?></span><b>↗</b></a>
            <?php if ($mobileLabel === 'services'): ?><a href="<?= htmlspecialchars(url('/subscription-plans')) ?>"><span>Subscription Plans</span><b>↗</b></a><?php endif; ?>
        <?php endforeach; ?>
        <?php if (!empty($header['resource_links'])): ?>
            <div class="mobile-resource-menu">
                <button type="button" aria-expanded="false"><span><?= htmlspecialchars($header['resources_label'] ?? 'Resources') ?></span><b>+</b></button>
                <div class="mobile-resource-links">
                    <?php foreach ($header['resource_links'] as $resourceItem): ?>
                        <a href="<?= htmlspecialchars(url($resourceItem['url'] ?? '#')) ?>"><i><?= htmlspecialchars($resourceItem['icon'] ?? '◇') ?></i><span><strong><?= htmlspecialchars($resourceItem['label'] ?? '') ?></strong><small><?= htmlspecialchars($resourceItem['description'] ?? '') ?></small></span></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </nav>
    <div class="mobile-menu-actions">
        <?php if (is_array($headerCustomer ?? null) && !empty($headerCustomer['id'])): ?>
            <a class="mobile-portal" href="<?= htmlspecialchars(url('/customer/dashboard')) ?>">My Dashboard</a>
            <form method="post" action="<?= htmlspecialchars(url('/customer/logout')) ?>"><?= csrf_field() ?><button type="submit" class="mobile-portal">Logout</button></form>
        <?php else: ?>
            <a class="mobile-portal" href="<?= htmlspecialchars(url($header['portal_url'] ?? '/customer/login')) ?>"><?= htmlspecialchars($portalLabel) ?></a>
        <?php endif; ?>
        <a class="mobile-consultation" href="<?= htmlspecialchars(url($header['consultation_url'] ?? '/contact')) ?>"><?= htmlspecialchars($header['consultation_label'] ?? 'Book Consultation') ?> <span>→</span></a>
    </div>
    <p class="mobile-menu-note">Empowering ideas. Building futures.</p>
</aside>
<?php require __DIR__ . '/consultation-modal.php'; ?>
<script>
(() => {
    const headerElement = document.querySelector('.site-header');
    if (!headerElement || headerElement.dataset.ready === 'true') return;
    headerElement.dataset.ready = 'true';
    const updateHeader = () => headerElement.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, {passive:true});
    document.querySelectorAll('.resource-trigger').forEach(trigger => trigger.addEventListener('click', event => {
        event.stopPropagation();
        const menu = trigger.closest('.resource-menu');
        const open = menu.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }));
    document.addEventListener('click', () => document.querySelectorAll('.resource-menu.is-open').forEach(menu => {
        menu.classList.remove('is-open');
        menu.querySelector('.resource-trigger')?.setAttribute('aria-expanded','false');
    }));
    const toggle = document.querySelector('.mobile-menu-toggle');
    const close = document.querySelector('.mobile-menu-close');
    const drawer = document.querySelector('.mobile-navigation');
    const overlay = document.querySelector('.mobile-menu-overlay');
    const setMenu = open => {
        document.body.classList.toggle('mobile-menu-open',open);
        drawer?.classList.toggle('is-open',open);
        overlay?.classList.toggle('is-open',open);
        drawer?.setAttribute('aria-hidden',open?'false':'true');
        overlay?.setAttribute('aria-hidden',open?'false':'true');
        toggle?.setAttribute('aria-expanded',open?'true':'false');
        if(open&&drawer){drawer.scrollTop=0;const nav=drawer.querySelector('nav');if(nav)nav.scrollTop=0;}
    };
    toggle?.addEventListener('click',event=>{event.stopPropagation();setMenu(true);});
    close?.addEventListener('click',()=>setMenu(false));
    overlay?.addEventListener('click',()=>setMenu(false));
    drawer?.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>setMenu(false)));
    const resources=document.querySelector('.mobile-resource-menu>button');
    resources?.addEventListener('click',()=>{
        const menu=resources.closest('.mobile-resource-menu');
        const expanded=menu?.classList.toggle('is-open')??false;
        resources.setAttribute('aria-expanded',expanded?'true':'false');
        const symbol=resources.querySelector('b');if(symbol)symbol.textContent=expanded?'−':'+';
    });
    window.addEventListener('keydown',event=>{if(event.key==='Escape')setMenu(false);});
    window.addEventListener('resize',()=>{if(window.innerWidth>1080)setMenu(false);},{passive:true});
    const accountTrigger=document.querySelector('.header-account-trigger');
    const account=document.querySelector('.header-account');
    accountTrigger?.addEventListener('click',event=>{
        event.stopPropagation();
        const open=account.classList.toggle('is-open');
        accountTrigger.setAttribute('aria-expanded',open?'true':'false');
    });
    document.addEventListener('click',()=>{account?.classList.remove('is-open');accountTrigger?.setAttribute('aria-expanded','false');});
})();
</script>