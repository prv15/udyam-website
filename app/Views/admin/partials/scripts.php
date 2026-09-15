<?php $adminJsVersion = (string) (@filemtime(ASSET_PATH . '/admin/js/app.js') ?: '1'); ?>
<script>window.udyamAdminAssets = {dashboard: <?= json_encode(url('/assets/admin/js/dashboard.js'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>};</script>
<script src="<?= htmlspecialchars(url('/assets/admin/js/app.js') . '?v=' . $adminJsVersion, ENT_QUOTES, 'UTF-8') ?>"></script>

<script>
    lucide.createIcons();
</script>
