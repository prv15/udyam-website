<?php

if (!function_exists('isActive')) {
    function isActive(string $route): string
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $currentPath = rtrim((string) (parse_url($currentUri, PHP_URL_PATH) ?: '/'), '/') ?: '/';
        // Resolve through url() so this also works when the CMS is installed in
        // a subdirectory such as /demo on a production server.
        $routePath = rtrim((string) (parse_url(url($route), PHP_URL_PATH) ?: '/'), '/') ?: '/';
        if (!str_starts_with($currentPath . '/', $routePath . '/')) return '';
        $routeQuery = (string) (parse_url($route, PHP_URL_QUERY) ?: '');
        if ($routeQuery !== '' && !str_contains((string) parse_url($currentUri, PHP_URL_QUERY), $routeQuery)) return '';
        return 'active';
    }
}

$menu = $adminMenu ?? require CONFIG_PATH . '/admin-menu.php';

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

        <button class="sidebar-mobile-close" type="button" id="sidebarMobileClose" aria-label="Close navigation">
            <i data-lucide="x"></i>
        </button>

    </div>

    <!-- Navigation -->
    <nav class="sidebar-menu">

        <?php foreach ($menu as $heading => $items): ?>

            <div class="menu-heading">
                <?= htmlspecialchars($heading) ?>
            </div>

            <?php foreach ($items as $item): ?>

                <?php if (($item['enabled'] ?? true) === false): continue; endif; ?>

                <?php if (!empty($item['children'])): ?>
                <details class="sidebar-menu-group"<?= isActive($item['route']) ? ' open' : '' ?>>
                    <summary class="menu-item <?= isActive($item['route']) ?>">

                    <i data-lucide="<?= htmlspecialchars($item['icon']) ?>"></i>

                    <span><?= htmlspecialchars($item['title']) ?></span>

                    <i data-lucide="chevron-down" class="menu-group-chevron"></i>
                    </summary>
                    <div class="sidebar-submenu"><?php foreach ($item['children'] as $child): ?><a href="<?= htmlspecialchars(url($child['route'])) ?>" class="<?= isActive($child['route']) ?>"><?= htmlspecialchars($child['title']) ?></a><?php endforeach; ?></div>
                </details>
                <?php else: ?>
                <a href="<?= htmlspecialchars(url($item['route'])) ?>" class="menu-item <?= isActive($item['route']) ?>">
                    <i data-lucide="<?= htmlspecialchars($item['icon']) ?>"></i><span><?= htmlspecialchars($item['title']) ?></span>
                </a>
                <?php endif; ?>

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

            <small><?= ($user['user_type'] ?? '') === 'staff' ? 'Staff Member' : 'Super Administrator' ?></small>

        </div>

        <form method="post" action="<?= htmlspecialchars(url('/admin/logout'), ENT_QUOTES, 'UTF-8') ?>">
            <?= csrf_field() ?>
        <button type="submit" class="logout-btn" title="Logout">
            <i data-lucide="log-out"></i>
        </button>
        </form>

    </div>

</aside>
<button class="sidebar-backdrop" type="button" id="sidebarBackdrop" aria-label="Close navigation" tabindex="-1"></button>
