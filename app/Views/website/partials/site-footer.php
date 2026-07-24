<?php if (!empty($footer) && is_array($footer)): ?>
<footer class="site-footer" id="site-footer">
    <div class="footer-main">
        <section class="footer-brand">
            <h2><?= htmlspecialchars($footer['brand_heading'] ?? 'Udyam Ventures') ?></h2>
            <p><?= nl2br(htmlspecialchars($footer['description'] ?? '')) ?></p>
            <div class="footer-socials">
                <?php foreach (($footer['social_links'] ?? []) as $social): ?>
                    <a href="<?= htmlspecialchars($social['url'] ?? '#') ?>" aria-label="<?= htmlspecialchars($social['label'] ?? 'Social profile') ?>" target="_blank" rel="noopener">
                        <?php $socialIcon = strtolower((string)($social['icon'] ?? '')); echo match($socialIcon) {
                            'linkedin'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9v10M6 5v.01M10 19v-6a4 4 0 0 1 8 0v6M10 9v10"/></svg>',
                            'facebook'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3a5 5 0 0 0-5 5v3H6v4h3v5h4v-5h3l1-4h-4V9a1 1 0 0 1 1-1Z"/></svg>',
                            'instagram'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg>',
                            'youtube'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12c0 3-.4 5-1 6-1 1-4 1-8 1s-7 0-8-1c-.6-1-1-3-1-6s.4-5 1-6c1-1 4-1 8-1s7 0 8 1c.6 1 1 3 1 6Z"/><path d="m10 9 5 3-5 3V9Z"/></svg>',
                            default=>'<span>↗</span>'}; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php foreach ([
            ['heading'=>$footer['services_heading']??'Services','links'=>$footer['service_links']??[]],
            ['heading'=>$footer['company_heading']??'Company','links'=>$footer['company_links']??[]],
            ['heading'=>$footer['resources_heading']??'Resources','links'=>$footer['resource_links']??[]],
        ] as $column): ?>
            <nav class="footer-column" aria-label="<?= htmlspecialchars((string)$column['heading']) ?>"><h2><?= htmlspecialchars((string)$column['heading']) ?></h2>
                <?php foreach($column['links'] as $link): ?><a href="<?= htmlspecialchars(url($link['url']??'#')) ?>"><?= htmlspecialchars($link['label']??'') ?></a><?php endforeach; ?>
            </nav>
        <?php endforeach; ?>
        <section class="footer-newsletter">
            <h2><?= htmlspecialchars($footer['newsletter_heading']??'Stay Updated') ?></h2><p><?= nl2br(htmlspecialchars($footer['newsletter_text']??'')) ?></p>
            <?php $subscription=(string)($_GET['subscription']??''); ?>
            <?php if($subscription==='success'): ?><p class="newsletter-status is-success" role="status">Thank you. You are now subscribed.</p><?php endif; ?>
            <?php if(in_array($subscription,['invalid','expired'],true)): ?><p class="newsletter-status is-error" role="alert"><?= $subscription==='invalid'?'Please enter a valid email address.':'Please refresh the page and try again.' ?></p><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(url('/newsletter/subscribe')) ?>"><?= csrf_field() ?><label class="sr-only" for="footer-email">Email address</label><input id="footer-email" name="email" type="email" autocomplete="email" placeholder="Enter your email" required maxlength="190"><button type="submit" aria-label="Subscribe to newsletter"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-9 20-2-9-9-2 20-9Z"/><path d="M22 2 11 13"/></svg></button></form>
        </section>
    </div>
    <div class="footer-bottom"><small><?= htmlspecialchars($footer['copyright']??'') ?></small><nav aria-label="Legal"><?php foreach(($footer['legal_links']??[]) as $link): ?><a href="<?= htmlspecialchars(url($link['url']??'#')) ?>"><?= htmlspecialchars($link['label']??'') ?></a><?php endforeach; ?></nav></div>
</footer>
<?php endif; ?>
<?php require __DIR__ . '/scroll-reveal.php'; ?>
