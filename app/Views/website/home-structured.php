<?php
$section = static fn (string $key): array => $sections[$key] ?? [];
$header = $section('header');
$hero = $section('hero');
$audiences = $section('audiences');
$stats = $section('impact_stats');
$tenderSection = $section('tenders');
$journey = $section('journey');
$why = $section('why_udyam');
$serviceSection = $section('services');
$focusSection = $section('focus_areas');
$ecosystem = $section('ecosystem');
$insight = $section('featured_insight');
$network = $section('ecosystem_network');
$knowledge = $section('knowledge_centre');
$partnerSection = $section('partners');
$closingCta = $section('closing_cta');
$footer = $section('footer');
$audienceIcon = static function (string $name): string {
    $paths = [
        'government' => '<path d="M3 21h18M5 18h14M6 10v8m4-8v8m4-8v8m4-8v8M3 7l9-4 9 4H3Z"/>',
        'csr' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/>',
        'msme' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/>',
        'startup' => '<path d="M5 19 19 5m-8 0h8v8"/><path d="M5 9v10h10"/>',
        'university' => '<path d="m3 10 9-5 9 5-9 5-9-5Z"/><path d="M7 12v5c3 2 7 2 10 0v-5M21 10v6"/>',
        'skills' => '<path d="M14.7 6.3a4 4 0 0 0-5 5L3 18l3 3 6.7-6.7a4 4 0 0 0 5-5l-2.4 2.4-3-3 2.4-2.4Z"/>',
    ];
    $path = $paths[$name] ?? $paths['skills'];
    return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
};
$impactIcon = static function (int $index): string {
    $paths = [
        '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0 1 12 0M13 15a5 5 0 0 1 8 4"/>',
        '<circle cx="12" cy="12" r="9"/><path d="M8 7h8M8 10h8M9 7c3.8 0 5.5 1.1 5.5 3.2S12.8 14 9 14h-.5l6 5M8 14h2"/>',
        '<path d="M4 21V8l8-5 8 5v13M2 21h20M8 21v-5h8v5M8 10h.01M12 10h.01M16 10h.01"/>',
        '<path d="M12 21s7-5.1 7-12A7 7 0 0 0 5 9c0 6.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.3"/>',
        '<path d="M4 20V10h16v10M7 10V7a5 5 0 0 1 10 0v3M8 14h8M12 10v10"/>',
        '<path d="M8 21h8M10 17h4v4M7 3h10v4a5 5 0 0 1-10 0V3ZM7 5H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$index] ?? $paths[0]) . '</svg>';
};
$journeyIcon = static function (string $name): string {
    $paths = [
        'idea' => '<path d="M9 18h6m-5 3h4M8.5 14.5A7 7 0 1 1 15.5 14.5C14.5 15.3 14 16 14 18h-4c0-2-.5-2.7-1.5-3.5Z"/>',
        'strategy' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><path d="m15 9 5-5m-1 0h1v1"/>',
        'planning' => '<path d="M6 3h9l3 3v15H6V3Z"/><path d="M15 3v4h4M9 11h6m-6 4h6"/>',
        'funding' => '<path d="M4 15c3-2 5-2 8 0l3-2c2-1 4 1 2 3l-5 4H4v-5Z"/><circle cx="14" cy="7" r="4"/><path d="M14 5v4m-1-3h2"/>',
        'execution' => '<path d="M12 3 9 6l-4-.5.5 4-3 2.5 3 2.5-.5 4 4-.5 3 3 3-3 4 .5-.5-4 3-2.5-3-2.5.5-4-4 .5-3-3Z"/><path d="m9.5 12 1.7 1.7 3.5-3.5"/>',
        'monitoring' => '<path d="M4 20V10m6 10V4m6 16v-7m4 7H2"/><path d="m4 8 6-5 6 8 4-4"/>',
        'impact' => '<path d="M8 21h8m-6-4h4v4M12 3a6 6 0 0 0-3 11v3h6v-3A6 6 0 0 0 12 3Z"/><path d="m10 10 2-2 2 2"/>',
    ];
    $path = $paths[$name] ?? $paths['idea'];
    return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
};
$focusIcon = static function (string $name): string {
    $paths = [
        'government' => '<path d="M3 21h18M5 18h14M6 10v8m4-8v8m4-8v8m4-8v8M3 7l9-4 9 4H3Z"/>',
        'industry' => '<path d="M3 21V9l6 4V9l6 4V5h6v16H3Z"/><path d="M7 17h2m4 0h2m4 0h2"/>',
        'skills' => '<path d="M14.7 6.3a4 4 0 0 0-5 5L3 18l3 3 6.7-6.7a4 4 0 0 0 5-5l-2.4 2.4-3-3 2.4-2.4Z"/>',
        'csr' => '<path d="M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11Z"/><path d="M8 13h8"/>',
        'research' => '<path d="m3 3 18 18M10 5a6 6 0 0 1 9 7l-3 3M5 10a6 6 0 0 0 7 9l3-3"/><circle cx="12" cy="12" r="2"/>',
        'startup' => '<path d="M14 4c3-2 6-1 6-1s1 3-1 6l-7 7-4-4 6-8Z"/><path d="M8 12 4 13l-2 3 6 1m4-1 1 4 3 2 1-6M9 19l-4 3 1-5"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['government']) . '</svg>';
};
$ecosystemIcon = static function (string $name): string {
    $paths = [
        'website' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
        'knowledge' => '<path d="M4 5c4-2 6 0 8 2v14c-2-2-4-4-8-2V5Zm16 0c-4-2-6 0-8 2v14c2-2 4-4 8-2V5Z"/>',
        'portal' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['website']) . '</svg>';
};
?>
<header class="site-header">
    <a class="site-brand" href="<?= htmlspecialchars(url('/')) ?>">
        <img src="<?= htmlspecialchars(!empty($header['logo_url']) ? $header['logo_url'] : url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures">
    </a>
    <nav class="desktop-navigation">
        <?php $resourceRendered = false; ?>
        <?php foreach (($header['navigation'] ?? []) as $item): ?>
            <?php
            $navLabel = strtolower(trim((string) ($item['label'] ?? '')));
            if (in_array($navLabel, ['knowledge centre', 'knowledge center', 'notice & tender', 'notice & tenders'], true)) continue;
            ?>
            <?php if ($navLabel === 'contact' && !empty($header['resource_links'])): $resourceRendered = true; ?>
                <?php require __DIR__ . '/partials/resource-menu.php'; ?>
            <?php endif; ?>
            <a href="<?= htmlspecialchars(url($item['url'] ?? '#')) ?>"><?= htmlspecialchars($item['label'] ?? '') ?></a>
        <?php endforeach; ?>
        <?php if (!$resourceRendered && !empty($header['resource_links'])): ?>
            <?php require __DIR__ . '/partials/resource-menu.php'; ?>
        <?php endif; ?>
    </nav>
    <div class="header-actions">
        <?php if (!empty($header['portal_label'])): ?><a class="portal-cta" href="<?= htmlspecialchars(url($header['portal_url'] ?? '#')) ?>"><?= htmlspecialchars($header['portal_label']) ?></a><?php endif; ?>
        <a class="site-cta" href="<?= htmlspecialchars(url($header['consultation_url'] ?? '/contact')) ?>"><?= htmlspecialchars($header['consultation_label'] ?? 'Book Consultation') ?> <span>→</span></a>
    </div>
    <button class="mobile-menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="mobile-navigation" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</header>
<div class="mobile-menu-overlay" aria-hidden="true"></div>
<aside class="mobile-navigation" id="mobile-navigation" aria-hidden="true">
    <div class="mobile-menu-head">
        <a class="mobile-menu-brand" href="<?= htmlspecialchars(url('/')) ?>">
            <img src="<?= htmlspecialchars(!empty($header['logo_url']) ? $header['logo_url'] : url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures">
        </a>
        <button class="mobile-menu-close" type="button" aria-label="Close navigation menu"><span></span><span></span></button>
    </div>
    <nav aria-label="Mobile navigation">
        <?php foreach (($header['navigation'] ?? []) as $item): ?>
            <?php $mobileLabel = strtolower(trim((string) ($item['label'] ?? ''))); ?>
            <?php if (in_array($mobileLabel, ['knowledge centre', 'knowledge center', 'notice & tender', 'notice & tenders'], true)) continue; ?>
            <a href="<?= htmlspecialchars(url($item['url'] ?? '#')) ?>"><span><?= htmlspecialchars($item['label'] ?? '') ?></span><b>↗</b></a>
        <?php endforeach; ?>
        <?php if (!empty($header['resource_links'])): ?>
            <div class="mobile-resource-menu">
                <button type="button" aria-expanded="false"><span><?= htmlspecialchars($header['resources_label'] ?? 'Resources') ?></span><b>+</b></button>
                <div class="mobile-resource-links">
                    <?php foreach ($header['resource_links'] as $resourceItem): ?>
                        <a href="<?= htmlspecialchars(url($resourceItem['url'] ?? '#')) ?>">
                            <i><?= htmlspecialchars($resourceItem['icon'] ?? '◇') ?></i>
                            <span><strong><?= htmlspecialchars($resourceItem['label'] ?? '') ?></strong><small><?= htmlspecialchars($resourceItem['description'] ?? '') ?></small></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </nav>
    <div class="mobile-menu-actions">
        <?php if (!empty($header['portal_label'])): ?><a class="mobile-portal" href="<?= htmlspecialchars(url($header['portal_url'] ?? '#')) ?>"><?= htmlspecialchars($header['portal_label']) ?></a><?php endif; ?>
        <a class="mobile-consultation" href="<?= htmlspecialchars(url($header['consultation_url'] ?? '/contact')) ?>"><?= htmlspecialchars($header['consultation_label'] ?? 'Book Consultation') ?> <span>→</span></a>
    </div>
    <p class="mobile-menu-note">Empowering ideas. Building futures.</p>
</aside>
<?php require __DIR__ . '/partials/consultation-modal.php'; ?>
<script>
(() => {
    const header = document.querySelector('.site-header');
    if (!header) return;
    const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
    document.querySelectorAll('.resource-trigger').forEach(trigger => {
        trigger.addEventListener('click', event => {
            event.stopPropagation();
            const menu = trigger.closest('.resource-menu');
            const open = menu.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.resource-menu.is-open').forEach(menu => {
            menu.classList.remove('is-open');
            menu.querySelector('.resource-trigger')?.setAttribute('aria-expanded', 'false');
        });
    });
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const menuClose = document.querySelector('.mobile-menu-close');
    const mobileMenu = document.querySelector('.mobile-navigation');
    const menuOverlay = document.querySelector('.mobile-menu-overlay');
    const setMobileMenu = open => {
        document.body.classList.toggle('mobile-menu-open', open);
        mobileMenu?.classList.toggle('is-open', open);
        menuOverlay?.classList.toggle('is-open', open);
        mobileMenu?.setAttribute('aria-hidden', open ? 'false' : 'true');
        menuOverlay?.setAttribute('aria-hidden', open ? 'false' : 'true');
        menuToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open && mobileMenu) {
            mobileMenu.scrollTop = 0;
            const mobileNav = mobileMenu.querySelector('nav');
            if (mobileNav) mobileNav.scrollTop = 0;
        }
    };
    menuToggle?.addEventListener('click', event => {
        event.stopPropagation();
        setMobileMenu(true);
    });
    menuClose?.addEventListener('click', () => setMobileMenu(false));
    menuOverlay?.addEventListener('click', () => setMobileMenu(false));
    mobileMenu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setMobileMenu(false)));
    const mobileResourceButton = document.querySelector('.mobile-resource-menu>button');
    mobileResourceButton?.addEventListener('click', () => {
        const resourceMenu = mobileResourceButton.closest('.mobile-resource-menu');
        const expanded = resourceMenu?.classList.toggle('is-open') ?? false;
        mobileResourceButton.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        const symbol = mobileResourceButton.querySelector('b');
        if (symbol) symbol.textContent = expanded ? '−' : '+';
    });
    window.addEventListener('keydown', event => {
        if (event.key === 'Escape') setMobileMenu(false);
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1080) setMobileMenu(false);
    }, { passive: true });
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('[data-tender-filter]');
        const rows = document.querySelectorAll('[data-tender-type]');
        const empty = document.querySelector('.tender-no-results');
        tabs.forEach(tab => tab.addEventListener('click', () => {
            const filter = tab.dataset.tenderFilter || 'all';
            tabs.forEach(item => {
                const active = item === tab;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            let visible = 0;
            rows.forEach(row => {
                const show = filter === 'all' || row.dataset.tenderType === filter;
                row.hidden = !show;
                if (show) visible++;
            });
            if (empty) empty.hidden = visible > 0;
        }));
    });
})();
</script>

<div class="hero-stage">
<?php if ($hero): ?><section class="home-hero">
    <div class="hero-copy">
        <p class="eyebrow"><?= htmlspecialchars($hero['eyebrow'] ?? '') ?></p>
        <h1><?= htmlspecialchars($hero['heading'] ?? '') ?><span><?= htmlspecialchars($hero['accent_heading'] ?? '') ?></span></h1>
        <p><?= nl2br(htmlspecialchars($hero['description'] ?? '')) ?></p>
        <div class="hero-actions">
            <?php if (!empty($hero['primary_cta_label'])): ?><a class="primary" href="<?= htmlspecialchars(url($hero['primary_cta_url'] ?? '#')) ?>"><?= htmlspecialchars($hero['primary_cta_label']) ?> →</a><?php endif; ?>
            <?php if (!empty($hero['secondary_cta_label'])): ?><a class="secondary" href="<?= htmlspecialchars(url($hero['secondary_cta_url'] ?? '#')) ?>"><?= htmlspecialchars($hero['secondary_cta_label']) ?> →</a><?php endif; ?>
        </div>
    </div>
    <div class="hero-visual">
        <?php if (!empty($hero['diagram_image_url'])): ?><img class="hero-mark" src="<?= htmlspecialchars($hero['diagram_image_url']) ?>" alt="Udyam Ventures strategy"><?php endif; ?>
        <?php if (!empty($hero['hero_image_url'])): ?><div class="hero-building-background" role="img" aria-label="Udyam Ventures" style="background-image:url('<?= htmlspecialchars($hero['hero_image_url'], ENT_QUOTES) ?>')"></div><?php endif; ?>
        <svg class="hero-connections" viewBox="0 0 1000 600" preserveAspectRatio="none" aria-hidden="true">
            <path d="M50 304 C95 250, 125 135, 180 98"></path>
            <path d="M50 304 C145 265, 220 195, 310 181"></path>
            <path d="M50 304 C115 295, 175 272, 230 267"></path>
            <path d="M50 304 C145 330, 220 348, 310 353"></path>
            <path d="M50 304 C95 360, 125 420, 170 440"></path>
            <circle cx="50" cy="304" r="5"></circle>
            <circle cx="180" cy="98" r="4"></circle>
            <circle cx="310" cy="181" r="4"></circle>
            <circle cx="230" cy="267" r="4"></circle>
            <circle cx="310" cy="353" r="4"></circle>
            <circle cx="170" cy="440" r="4"></circle>
        </svg>
        <div class="hero-capability-list">
            <?php foreach (($hero['capability_cards'] ?? []) as $index => $item): ?>
                <article class="hero-capability-card capability-<?= (int) $index + 1 ?>">
                    <span class="capability-icon"><?= htmlspecialchars(($item['icon'] ?? '◇') === '$' ? '₹' : ($item['icon'] ?? '◇')) ?></span>
                    <div><strong><?= htmlspecialchars($item['title'] ?? '') ?></strong><small><?= htmlspecialchars($item['description'] ?? '') ?></small></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section><?php endif; ?>

<?php if (!empty($audiences['items'])): ?><section class="audience-grid">
    <?php foreach ($audiences['items'] as $item): ?><a href="<?= htmlspecialchars(url($item['url'] ?? '#')) ?>"><span><?= $audienceIcon((string) ($item['icon'] ?? 'skills')) ?></span><strong><?= htmlspecialchars($item['title'] ?? '') ?></strong><i>Explore →</i></a><?php endforeach; ?>
</section><?php endif; ?>
</div>

<?php if (!empty($stats['items'])): ?><section class="impact-strip">
    <?php foreach ($stats['items'] as $index => $item): ?><div><span><?= $impactIcon((int) $index) ?></span><strong><?= htmlspecialchars($item['value'] ?? '') ?></strong><small><?= htmlspecialchars($item['label'] ?? '') ?></small></div><?php endforeach; ?>
</section><?php endif; ?>

<?php if ($tenderSection): ?><section class="tenders-section" id="tenders">
    <aside class="tender-intro">
        <span class="tender-kicker">Government Updates</span>
        <h2><?= htmlspecialchars($tenderSection['heading'] ?? 'Notices & Tenders') ?></h2>
        <p><?= nl2br(htmlspecialchars($tenderSection['description'] ?? '')) ?></p>
        <a href="#tender-list"><?= htmlspecialchars($tenderSection['button_label'] ?? 'View All Tenders') ?> <span>→</span></a>
        <svg class="tender-megaphone" viewBox="0 0 120 120" aria-hidden="true">
            <path d="M20 55h20l42-24v58L40 65H20V55Z"/><path d="m40 65 8 25H33l-8-25M91 48l13-8m-13 32 13 8M94 60h16"/>
        </svg>
    </aside>
    <div class="tender-content" id="tender-list">
        <div class="tender-heading">
            <div><span>Latest Updates</span><h2><?= htmlspecialchars($tenderSection['heading'] ?? 'Notices & Tenders') ?></h2></div>
            <div class="tender-tabs" role="tablist" aria-label="Filter notices and tenders">
                <button class="is-active" type="button" data-tender-filter="all" role="tab" aria-selected="true">All</button>
                <button type="button" data-tender-filter="tender" role="tab" aria-selected="false">Tenders</button>
                <button type="button" data-tender-filter="notice" role="tab" aria-selected="false">Notices</button>
                <button type="button" data-tender-filter="corrigendum" role="tab" aria-selected="false">Corrigendum</button>
            </div>
        </div>
        <div class="tender-table"><table><thead><tr><th>Title</th><th>Department</th><th>Closing Date</th><th aria-label="Actions"></th></tr></thead><tbody>
            <?php foreach ($tenders as $item): ?>
                <?php
                $closingDate = trim((string) ($item['closing_date'] ?? ''));
                $dateLabel = $closingDate;
                if ($closingDate !== '') {
                    try { $dateLabel = (new DateTimeImmutable($closingDate))->format('d M Y'); } catch (Throwable) {}
                }
                ?>
                <tr data-tender-type="<?= htmlspecialchars(strtolower((string) ($item['type'] ?? 'tender'))) ?>">
                    <td data-label="Title"><strong><?= htmlspecialchars($item['title']) ?></strong><small><?= htmlspecialchars(ucfirst((string) ($item['type'] ?? 'Tender'))) ?></small></td>
                    <td data-label="Department"><?= htmlspecialchars($item['department'] ?? '') ?></td>
                    <td data-label="Closing Date"><time datetime="<?= htmlspecialchars($closingDate) ?>"><?= htmlspecialchars($dateLabel) ?></time></td>
                    <td><a class="tender-detail" href="<?= htmlspecialchars($item['document_url'] ?? '#') ?>">View Details <span>→</span></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($tenders === []): ?><tr class="tender-empty"><td colspan="4">No active notices or tenders.</td></tr><?php endif; ?>
            <tr class="tender-no-results" hidden><td colspan="4">No updates are available in this category.</td></tr>
        </tbody></table></div>
        <a class="tender-all-button" href="#tender-list">View All Tenders &amp; Notices <span>→</span></a>
    </div>
</section><?php endif; ?>

<section class="two-column journey-why-section">
    <?php if ($journey): ?><div class="home-card journey-card"><h2><?= htmlspecialchars($journey['heading'] ?? 'Our Journey With You') ?></h2><div class="journey-row">
        <?php foreach (($journey['items'] ?? []) as $index => $item): ?><article>
            <span class="journey-icon tone-<?= ($index % 4) + 1 ?>"><?= $journeyIcon((string) ($item['icon'] ?? 'idea')) ?></span>
            <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
            <small><?= htmlspecialchars($item['description'] ?? '') ?></small>
            <?php if ($index < count($journey['items']) - 1): ?><i class="journey-arrow">→</i><?php endif; ?>
        </article><?php endforeach; ?>
    </div></div><?php endif; ?>
    <?php if ($why): ?><div class="home-card why-card"><div><h2><?= htmlspecialchars($why['heading'] ?? 'Why Udyam Ventures?') ?></h2><ul><?php foreach (($why['items'] ?? []) as $index => $item): ?><li class="reason-tone-<?= ($index % 2) + 1 ?>"><span>✓</span><?= htmlspecialchars($item['text'] ?? '') ?></li><?php endforeach; ?></ul></div><?php if (!empty($why['artwork_url'])): ?><div class="why-artwork"><img src="<?= htmlspecialchars($why['artwork_url']) ?>" alt="Why Udyam Ventures"></div><?php endif; ?></div><?php endif; ?>
</section>

<?php if ($serviceSection): ?><section class="home-section services-section" id="services"><div class="service-heading"><span></span><h2><?= htmlspecialchars($serviceSection['heading'] ?? 'Our Core Services') ?></h2><span></span></div><div class="service-grid">
    <?php foreach ($services as $index => $item): ?><article class="service-card service-tone-<?= ($index % 4) + 1 ?>" tabindex="0">
        <div class="service-image"><?php if (!empty($item['image'])): ?><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>"><?php endif; ?><span class="service-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="service-card-body"><h3><span><?= htmlspecialchars(($item['icon'] ?? '◇') === '$' ? '₹' : ($item['icon'] ?? '◇')) ?></span><?= htmlspecialchars($item['title']) ?></h3><p><?= htmlspecialchars($item['summary'] ?? '') ?></p><a href="<?= htmlspecialchars(url('/services#' . ($item['slug'] ?? $item['id']))) ?>">Read More <span>→</span></a></div>
    </article><?php endforeach; ?>
</div></section><?php endif; ?>

<section class="three-column focus-ecosystem-section">
    <?php if ($focusSection): ?><div class="home-card focus-panel" id="focus-areas"><h2><?= htmlspecialchars($focusSection['heading'] ?? 'Focus Areas') ?></h2><div class="focus-list"><?php foreach ($focusAreas as $item): ?><a href="<?= htmlspecialchars(url('/focus-areas/' . ($item['slug'] ?? $item['id']))) ?>"><span><?= $focusIcon((string) ($item['icon'] ?? 'government')) ?></span><strong><?= htmlspecialchars($item['title']) ?></strong><i>→</i></a><?php endforeach; ?></div><a class="panel-link" href="<?= htmlspecialchars(url($focusSection['button_url'] ?? '/#focus-areas')) ?>"><?= htmlspecialchars($focusSection['button_label'] ?? 'Explore All Focus Areas') ?> <span>→</span></a></div><?php endif; ?>
    <?php if ($ecosystem): ?><div class="home-card ecosystem-panel"><h2><?= htmlspecialchars($ecosystem['heading'] ?? 'Our Digital Ecosystem') ?></h2><p><?= htmlspecialchars($ecosystem['tagline'] ?? '') ?></p><div class="ecosystem-row"><?php foreach (($ecosystem['items'] ?? []) as $index => $item): ?><a href="<?= htmlspecialchars(url($item['url'] ?? '#')) ?>"><span><?= $ecosystemIcon((string) ($item['icon'] ?? 'website')) ?></span><strong><?= htmlspecialchars($item['title'] ?? '') ?></strong><?php if ($index < count($ecosystem['items']) - 1): ?><i>···→</i><?php endif; ?></a><?php endforeach; ?></div><a class="panel-link" href="<?= htmlspecialchars(url($ecosystem['button_url'] ?? '/#knowledge-centre')) ?>"><?= htmlspecialchars($ecosystem['button_label'] ?? 'Explore Digital Platforms') ?> <span>→</span></a></div><?php endif; ?>
    <?php if ($insight): ?><div class="featured-insight insight-panel" id="featured-insight"><div><span class="insight-label">Featured Insight</span><small><?= htmlspecialchars($insight['kicker'] ?? '') ?></small><h2><?= htmlspecialchars($insight['heading'] ?? '') ?></h2><p><?= htmlspecialchars($insight['description'] ?? '') ?></p><a href="<?= htmlspecialchars($insight['button_url'] ?? '#') ?>"><?= htmlspecialchars($insight['button_label'] ?? 'Read More') ?> <span>→</span></a></div><?php if (!empty($insight['cover_image_url'])): ?><div class="insight-cover"><img src="<?= htmlspecialchars($insight['cover_image_url']) ?>" alt="<?= htmlspecialchars($insight['heading'] ?? 'Featured insight') ?>"></div><?php endif; ?></div><?php endif; ?>
</section>

<?php if ($partnerSection): ?><section class="partners"><h2><?= htmlspecialchars($partnerSection['heading'] ?? 'Our Partners') ?></h2><?php foreach ($partners as $item): ?><a href="<?= htmlspecialchars($item['website'] ?? '#') ?>"><?= htmlspecialchars($item['title']) ?></a><?php endforeach; ?></section><?php endif; ?>

<?php if ($network): ?><section class="network-section" id="resource-center">
    <div class="section-heading"><small><?= htmlspecialchars($network['eyebrow'] ?? '') ?></small><h2><?= htmlspecialchars($network['heading'] ?? '') ?></h2><p><?= htmlspecialchars($network['description'] ?? '') ?></p></div>
    <div class="network-grid"><?php foreach (($network['items'] ?? []) as $item): ?><article><span><?= htmlspecialchars(($item['icon'] ?? '◇') === '$' ? '₹' : ($item['icon'] ?? '◇')) ?></span><h3><?= htmlspecialchars($item['title'] ?? '') ?></h3><p><?= htmlspecialchars($item['description'] ?? '') ?></p></article><?php endforeach; ?></div>
    <div class="sector-spotlight"><div><small>Impact Ecosystem</small><h2><?= htmlspecialchars($network['featured_title'] ?? '') ?></h2><p><?= htmlspecialchars($network['featured_description'] ?? '') ?></p></div><div class="sector-stats"><?php foreach (($network['featured_stats'] ?? []) as $stat): ?><div><strong><?= htmlspecialchars($stat['value'] ?? '') ?></strong><span><?= htmlspecialchars($stat['label'] ?? '') ?></span></div><?php endforeach; ?></div></div>
</section><?php endif; ?>

<?php if ($knowledge): ?><section class="knowledge-section" id="knowledge-centre">
    <div class="section-heading"><small><?= htmlspecialchars($knowledge['eyebrow'] ?? '') ?></small><h2><?= htmlspecialchars($knowledge['heading'] ?? '') ?></h2><p><?= htmlspecialchars($knowledge['description'] ?? '') ?></p></div>
    <div class="insight-grid"><?php foreach ($insights as $item): ?><article><small><?= htmlspecialchars($item['category'] ?? 'Insight') ?></small><h3><?= htmlspecialchars($item['title']) ?></h3><p><?= htmlspecialchars($item['excerpt'] ?? '') ?></p><a href="<?= htmlspecialchars(url('/blog/' . ($item['slug'] ?? $item['id']))) ?>">Read Insight →</a></article><?php endforeach; ?></div>
</section><?php endif; ?>

<?php if ($closingCta): ?><section class="closing-cta"><div><h2><?= htmlspecialchars($closingCta['heading'] ?? '') ?></h2><p><?= htmlspecialchars($closingCta['description'] ?? '') ?></p></div><a href="<?= htmlspecialchars(url($closingCta['button_url'] ?? '#')) ?>"><?= htmlspecialchars($closingCta['button_label'] ?? 'Get Started') ?> →</a></section><?php endif; ?>

<?php if ($footer): ?>
<footer class="site-footer" id="site-footer">
    <div class="footer-main">
        <section class="footer-brand">
            <h2><?= htmlspecialchars($footer['brand_heading'] ?? 'Udyam Ventures') ?></h2>
            <p><?= nl2br(htmlspecialchars($footer['description'] ?? '')) ?></p>
            <div class="footer-socials">
                <?php foreach (($footer['social_links'] ?? []) as $social): ?>
                    <a href="<?= htmlspecialchars($social['url'] ?? '#') ?>" aria-label="<?= htmlspecialchars($social['label'] ?? 'Social profile') ?>" target="_blank" rel="noopener">
                        <?php
                        $socialIcon = strtolower((string) ($social['icon'] ?? ''));
                        echo match ($socialIcon) {
                            'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9v10M6 5v.01M10 19v-6a4 4 0 0 1 8 0v6M10 9v10"/></svg>',
                            'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3a5 5 0 0 0-5 5v3H6v4h3v5h4v-5h3l1-4h-4V9a1 1 0 0 1 1-1Z"/></svg>',
                            'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg>',
                            'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12c0 3-.4 5-1 6-1 1-4 1-8 1s-7 0-8-1c-.6-1-1-3-1-6s.4-5 1-6c1-1 4-1 8-1s7 0 8 1c.6 1 1 3 1 6Z"/><path d="m10 9 5 3-5 3V9Z"/></svg>',
                            default => '<span>↗</span>',
                        };
                        ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php foreach ([
            ['heading' => $footer['services_heading'] ?? 'Services', 'links' => $footer['service_links'] ?? []],
            ['heading' => $footer['company_heading'] ?? 'Company', 'links' => $footer['company_links'] ?? []],
            ['heading' => $footer['resources_heading'] ?? 'Resources', 'links' => $footer['resource_links'] ?? []],
        ] as $column): ?>
            <nav class="footer-column" aria-label="<?= htmlspecialchars((string) $column['heading']) ?>">
                <h2><?= htmlspecialchars((string) $column['heading']) ?></h2>
                <?php foreach ($column['links'] as $link): ?>
                    <a href="<?= htmlspecialchars(url($link['url'] ?? '#')) ?>"><?= htmlspecialchars($link['label'] ?? '') ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endforeach; ?>

        <section class="footer-newsletter">
            <h2><?= htmlspecialchars($footer['newsletter_heading'] ?? 'Stay Updated') ?></h2>
            <p><?= nl2br(htmlspecialchars($footer['newsletter_text'] ?? '')) ?></p>
            <?php $subscription = (string) ($_GET['subscription'] ?? ''); ?>
            <?php if ($subscription === 'success'): ?><p class="newsletter-status is-success" role="status">Thank you. You are now subscribed.</p><?php endif; ?>
            <?php if (in_array($subscription, ['invalid', 'expired'], true)): ?><p class="newsletter-status is-error" role="alert"><?= $subscription === 'invalid' ? 'Please enter a valid email address.' : 'Please refresh the page and try again.' ?></p><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(url('/newsletter/subscribe')) ?>">
                <?= csrf_field() ?>
                <label class="sr-only" for="footer-email">Email address</label>
                <input id="footer-email" name="email" type="email" autocomplete="email" placeholder="Enter your email" required maxlength="190">
                <button type="submit" aria-label="Subscribe to newsletter">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-9 20-2-9-9-2 20-9Z"/><path d="M22 2 11 13"/></svg>
                </button>
            </form>
        </section>
    </div>
    <div class="footer-bottom">
        <small><?= htmlspecialchars($footer['copyright'] ?? '') ?></small>
        <nav aria-label="Legal">
            <?php foreach (($footer['legal_links'] ?? []) as $link): ?><a href="<?= htmlspecialchars(url($link['url'] ?? '#')) ?>"><?= htmlspecialchars($link['label'] ?? '') ?></a><?php endforeach; ?>
        </nav>
    </div>
</footer>
<?php endif; ?>
<?php require __DIR__ . '/partials/scroll-reveal.php'; ?>
