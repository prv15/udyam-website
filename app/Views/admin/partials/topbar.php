<header class="topbar">

    <div class="topbar-left">

        <button class="sidebar-toggle" id="sidebarToggle">

            <i data-lucide="panel-left-close"></i>

        </button>

        <div class="page-title">

            <h1><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>

            <small>Welcome back, <?= htmlspecialchars($user['first_name']) ?></small>

        </div>

    </div>


    <div class="topbar-center">

        <div class="search-box global-search" data-search-url="<?= htmlspecialchars(url('/admin/search')) ?>">

            <i data-lucide="search"></i>

            <input
                type="text"
                id="adminGlobalSearch"
                placeholder="Search customers, invoices, pages, services..."
                autocomplete="off"
                role="combobox"
                aria-expanded="false"
                aria-controls="adminSearchResults"
                aria-autocomplete="list">

            <kbd>⌘ K</kbd>
            <div class="global-search-results" id="adminSearchResults" role="listbox" hidden></div>

        </div>

    </div>


    <div class="topbar-right">

        <div class="create-menu">
            <button class="quick-btn create-menu-trigger" type="button" aria-expanded="false" aria-controls="adminCreateMenu">
                <i data-lucide="plus"></i>
                <span>New</span>
                <i data-lucide="chevron-down" class="create-chevron"></i>
            </button>
            <div class="create-menu-panel" id="adminCreateMenu" hidden>
                <div class="create-menu-heading"><span>Quick create</span><small>Start a new record</small></div>
                <div class="create-menu-grid">
                    <?php
                    $createItems = [
                        ['/admin/pages/create', 'file-plus-2', 'Page', 'Website content'],
                        ['/admin/subscription-plans/create', 'badge-indian-rupee', 'Subscription', 'Customer plan'],
                        ['/admin/services/create', 'briefcase-business', 'Service', 'Service catalogue'],
                        ['/admin/customers/create', 'user-plus', 'Customer', 'Portal customer'],
                        ['/admin/applications/create', 'clipboard-plus', 'Application', 'Service request'],
                        ['/admin/tenders/create', 'megaphone', 'Tender / Notice', 'Latest update'],
                        ['/admin/blog/create', 'newspaper', 'Article', 'Knowledge content'],
                        ['/admin/media', 'image-plus', 'Media', 'Upload an asset'],
                    ];
                    foreach ($createItems as [$route, $icon, $label, $description]):
                    ?>
                        <a href="<?= htmlspecialchars(url($route)) ?>">
                            <span><i data-lucide="<?= htmlspecialchars($icon) ?>"></i></span>
                            <span><strong><?= htmlspecialchars($label) ?></strong><small><?= htmlspecialchars($description) ?></small></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <a class="icon-btn notification-button<?= ($unreadContactCount ?? 0) > 0 ? ' has-notifications' : '' ?>" href="<?= htmlspecialchars(url('/admin/contact-messages')) ?>" aria-label="<?= (int) ($unreadContactCount ?? 0) ?> unread contact messages">

            <i data-lucide="bell"></i>
            <?php if (($unreadContactCount ?? 0) > 0): ?><span class="notification-count"><?= min(99, (int) $unreadContactCount) ?></span><?php endif; ?>

        </a>

        <button class="icon-btn">

            <i data-lucide="moon"></i>

        </button>

        <div class="profile-dropdown">

            <div class="profile-avatar">

                <?= strtoupper(substr($user['first_name'],0,1)); ?>

            </div>

            <div class="profile-info">

                <strong><?= htmlspecialchars($user['first_name']) ?></strong>

                <small>Administrator</small>

            </div>

            <i data-lucide="chevron-down"></i>

        </div>

    </div>

</header>
