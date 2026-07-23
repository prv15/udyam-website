<?php
$hero = $sections['page_hero'] ?? [];
$blocks = $sections['content_blocks']['items'] ?? [];
$features = $sections['feature_grid'] ?? [];
$stats = $sections['page_stats']['items'] ?? [];
$faqs = $sections['page_faqs'] ?? [];
$cta = $sections['page_cta'] ?? [];
?>
<?php require __DIR__ . '/partials/site-header.php'; ?>
<main class="sectioned-page">
    <?php if ($hero): ?>
        <section class="inner-hero<?= !empty($hero['image_url']) ? ' has-background' : '' ?>"<?php if (!empty($hero['image_url'])): ?> style="background-image:url('<?= htmlspecialchars($hero['image_url'], ENT_QUOTES) ?>')"<?php endif; ?>>
            <div class="inner-hero-content">
                <small><?= htmlspecialchars($hero['eyebrow'] ?? '') ?></small>
                <h1><?= htmlspecialchars($hero['heading'] ?? $page['title']) ?></h1>
                <p><?= nl2br(htmlspecialchars($hero['description'] ?? '')) ?></p>
                <?php if (!empty($hero['primary_button_label']) || !empty($hero['secondary_button_label'])): ?>
                    <div class="inner-hero-actions">
                        <?php if (!empty($hero['primary_button_label'])): ?><a class="primary" href="<?= htmlspecialchars(url($hero['primary_button_url'] ?? '#')) ?>"><?= htmlspecialchars($hero['primary_button_label']) ?> <span>→</span></a><?php endif; ?>
                        <?php if (!empty($hero['secondary_button_label'])): ?><a class="secondary" href="<?= htmlspecialchars(url($hero['secondary_button_url'] ?? '#')) ?>"><?= htmlspecialchars($hero['secondary_button_label']) ?> <span>→</span></a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php foreach ($blocks as $index => $block): ?><section class="content-block <?= $index % 2 ? 'reverse' : '' ?>"><div><h2><?= htmlspecialchars($block['heading'] ?? '') ?></h2><p><?= nl2br(htmlspecialchars($block['content'] ?? '')) ?></p></div><?php if (!empty($block['image_url'])): ?><img src="<?= htmlspecialchars($block['image_url']) ?>" alt=""><?php endif; ?></section><?php endforeach; ?>
    <?php if ($features): ?><section class="inner-section"><div class="section-heading"><h2><?= htmlspecialchars($features['heading'] ?? '') ?></h2><p><?= htmlspecialchars($features['description'] ?? '') ?></p></div><div class="feature-grid"><?php foreach (($features['items'] ?? []) as $item): ?><article><span><?= htmlspecialchars($item['icon'] ?? '◇') ?></span><h3><?= htmlspecialchars($item['title'] ?? '') ?></h3><p><?= htmlspecialchars($item['description'] ?? '') ?></p></article><?php endforeach; ?></div></section><?php endif; ?>
    <?php if ($stats): ?><section class="page-stat-strip"><?php foreach ($stats as $stat): ?><div><strong><?= htmlspecialchars($stat['value'] ?? '') ?></strong><span><?= htmlspecialchars($stat['label'] ?? '') ?></span></div><?php endforeach; ?></section><?php endif; ?>
    <?php if ($faqs): ?><section class="inner-section faq-section"><div class="section-heading"><h2><?= htmlspecialchars($faqs['heading'] ?? '') ?></h2></div><?php foreach (($faqs['items'] ?? []) as $item): ?><details><summary><?= htmlspecialchars($item['question'] ?? '') ?></summary><p><?= nl2br(htmlspecialchars($item['answer'] ?? '')) ?></p></details><?php endforeach; ?></section><?php endif; ?>
    <?php if ($cta): ?><section class="closing-cta"><div><h2><?= htmlspecialchars($cta['heading'] ?? '') ?></h2><p><?= htmlspecialchars($cta['description'] ?? '') ?></p></div><a href="<?= htmlspecialchars(url($cta['button_url'] ?? '#')) ?>"><?= htmlspecialchars($cta['button_label'] ?? 'Get Started') ?> →</a></section><?php endif; ?>
</main>
<?php require __DIR__ . '/partials/site-footer.php'; ?>
