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

        <div class="search-box">

            <i data-lucide="search"></i>

            <input
                type="text"
                placeholder="Search pages, services, blogs...">

        </div>

    </div>


    <div class="topbar-right">

        <a class="quick-btn" href="<?= htmlspecialchars(url('/admin/pages/create')) ?>">

            <i data-lucide="plus"></i>

            <span>New</span>

        </a>

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
