<?php
$loginCssVersion = (string) (@filemtime(ASSET_PATH . '/admin/css/login.css') ?: '1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Secure Admin Login | Udyam Ventures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(url('/assets/admin/css/login.css')) ?>?v=<?= rawurlencode($loginCssVersion) ?>">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-showcase" aria-label="Udyam Ventures enterprise platform">
            <div class="showcase-glow showcase-glow-one"></div>
            <div class="showcase-glow showcase-glow-two"></div>

            <a class="login-brand" href="<?= htmlspecialchars(url('/')) ?>">
                <img src="<?= htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures">
            </a>

            <div class="showcase-content">
                <span class="showcase-kicker"><i data-lucide="shield-check"></i> Enterprise Management Suite</span>
                <h1>One secure workspace.<br><span>Complete operational control.</span></h1>
                <p>Manage website content, customer journeys, enquiries, applications and publishing from one unified platform.</p>

                <div class="showcase-features">
                    <div><i data-lucide="layout-dashboard"></i><span><strong>Unified CMS</strong><small>Every website module in one place</small></span></div>
                    <div><i data-lucide="users-round"></i><span><strong>Customer Operations</strong><small>Applications, documents and updates</small></span></div>
                    <div><i data-lucide="activity"></i><span><strong>Live Intelligence</strong><small>Enquiries and activity at a glance</small></span></div>
                </div>
            </div>

            <p class="showcase-footer">Protected access · Udyam Ventures</p>
        </section>

        <section class="login-form-side">
            <div class="mobile-brand">
                <img src="<?= htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures">
            </div>

            <div class="login-card">
                <div class="login-card-heading">
                    <span class="secure-mark"><i data-lucide="lock-keyhole"></i></span>
                    <div>
                        <p class="eyebrow">Administrator portal</p>
                        <h2>Welcome back</h2>
                        <p>Sign in to continue to your secure workspace.</p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="login-alert" role="alert">
                        <i data-lucide="circle-alert"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars(url('/admin/login')) ?>" class="login-form">
                    <?= csrf_field() ?>

                    <label for="email">Work email</label>
                    <div class="login-input">
                        <i data-lucide="mail"></i>
                        <input id="email" type="email" name="email" value="<?= htmlspecialchars((string) ($_POST['email'] ?? '')) ?>" placeholder="name@udyamventures.com" autocomplete="username" required autofocus>
                    </div>

                    <div class="password-label">
                        <label for="password">Password</label>
                    </div>
                    <div class="login-input">
                        <i data-lucide="key-round"></i>
                        <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>

                    <button class="login-submit" type="submit">
                        <span>Sign in securely</span>
                        <i data-lucide="arrow-right"></i>
                    </button>
                </form>

                <p class="login-support"><i data-lucide="shield"></i> Authorised administrators only. Access is monitored and protected.</p>
            </div>
        </section>
    </main>

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) window.lucide.createIcons();
            var toggle = document.querySelector('.password-toggle');
            var password = document.getElementById('password');
            if (!toggle || !password) return;
            toggle.addEventListener('click', function () {
                var reveal = password.type === 'password';
                password.type = reveal ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
                toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
                toggle.innerHTML = '<i data-lucide="' + (reveal ? 'eye-off' : 'eye') + '"></i>';
                if (window.lucide) window.lucide.createIcons();
            });
        });
    </script>
</body>
</html>
