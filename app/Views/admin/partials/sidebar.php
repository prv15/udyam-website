<?php

if (!function_exists('isActive')) {
    function isActive(string $route): string
    {
        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        return strpos($currentUri, $route) !== false ? 'active' : '';
    }
}

$menu = require CONFIG_PATH . '/admin-menu.php';

?>

<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">

        <a href="<?= htmlspecialchars(url('/admin/dashboard'), ENT_QUOTES, 'UTF-8') ?>">

            <div class="logo-icon">
                <i data-lucide="layers-3"></i>
            </div>

            <div class="logo-text">
                <h2>Udyam Ventures</h2>
                <small>Enterprise CMS</small>
            </div>

        </a>

    </div>

    <!-- Navigation -->
    <nav class="sidebar-menu">

        <?php foreach ($menu as $heading => $items): ?>

            <div class="menu-heading">
                <?= htmlspecialchars($heading) ?>
            </div>

            <?php foreach ($items as $item): ?>

                <?php if (($item['enabled'] ?? true) === false): continue; endif; ?>

                <a
                    href="<?= htmlspecialchars(url($item['route'])) ?>"
                    class="menu-item <?= isActive($item['route']) ?>"
                >

                    <i data-lucide="<?= htmlspecialchars($item['icon']) ?>"></i>

                    <span><?= htmlspecialchars($item['title']) ?></span>

                </a>

            <?php endforeach; ?>

        <?php endforeach; ?>

    </nav>

    <!-- User -->
    <div class="sidebar-user">

        <div class="avatar">
            <?= strtoupper(substr($user['first_name'], 0, 1)); ?>
        </div>

        <div class="user-info">

            <strong>
                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
            </strong>

            <small>Super Administrator</small>

        </div>

        <form method="post" action="<?= htmlspecialchars(url('/admin/logout'), ENT_QUOTES, 'UTF-8') ?>">
            <?= csrf_field() ?>
        <button type="submit" class="logout-btn" title="Logout">
            <i data-lucide="log-out"></i>
        </button>
        </form>

    </div>

</aside>
