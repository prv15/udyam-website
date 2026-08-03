<?php
$planBenefits = static function (mixed $value): array {
    $raw = trim((string) $value);
    $decoded = $raw === '' ? null : json_decode($raw, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map(static function (mixed $item): ?array {
            if (is_array($item)) {
                $title = trim((string) ($item['title'] ?? $item['name'] ?? ''));
                return $title === '' ? null : ['title' => $title, 'description' => trim((string) ($item['description'] ?? ''))];
            }
            $title = trim((string) $item);
            return $title === '' ? null : ['title' => $title, 'description' => ''];
        }, $decoded)));
    }
    return array_values(array_filter(array_map(static function (string $line): ?array {
        [$title, $description] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
        return $title === '' ? null : ['title' => $title, 'description' => $description];
    }, preg_split('/\r?\n/', $raw) ?: [])));
};
?>
<?php require __DIR__.'/partials/site-header.php'; ?>
<main class="public-plans"><header><span>Memberships</span><h1>Plans that keep opportunity within reach.</h1><p>Choose a Udyam subscription to unlock curated notices, tenders, practical guidance and your secure client workspace.</p></header><section class="public-plan-grid"><?php foreach($plans as $plan):$benefits=$planBenefits($plan['benefits']??'');?><article class="public-plan-card"><div class="plan-card-head"><small><?=htmlspecialchars($plan['category']??'Membership')?></small><h2><?=htmlspecialchars($plan['name'])?></h2><p><?=htmlspecialchars($plan['subtitle']??'')?></p></div><div class="plan-price"><strong>₹<?=number_format((float)($plan['price']??0),0)?></strong><em>/ <?=htmlspecialchars($plan['billing_cycle']??'month')?></em></div><div class="plan-divider"></div><h3>What’s included</h3><ul><?php foreach($benefits as $benefit):?><li><span>✓</span><div><b><?=htmlspecialchars($benefit['title'])?></b><?php if($benefit['description']!==''):?><small><?=htmlspecialchars($benefit['description'])?></small><?php endif;?></div></li><?php endforeach;?></ul><a href="<?=htmlspecialchars(url('/customer/plans'))?>">Subscribe Now <span>→</span></a></article><?php endforeach;?></section></main>
<?php require __DIR__.'/partials/site-footer.php'; ?>
