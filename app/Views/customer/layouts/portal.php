<?php $cssVersion=(string)(@filemtime(ASSET_PATH.'/customer/portal.css')?:'1'); $uri=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)?:''; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=htmlspecialchars($title)?> | Udyam Partner Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=htmlspecialchars(url('/assets/customer/portal.css'))?>?v=<?=rawurlencode($cssVersion)?>"><script src="https://unpkg.com/lucide@latest"></script></head><body class="portal-body">
<aside class="portal-sidebar"><a class="portal-brand" href="<?=htmlspecialchars(url('/customer/dashboard'))?>"><img src="<?=htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png'))?>" alt="Udyam Ventures"></a>
<nav><?php foreach([
'/customer/dashboard'=>['layout-dashboard','Dashboard'],'/customer/profile'=>['building-2','Company Profile'],'/customer/plans'=>['sparkles','Subscriptions'],'/customer/services'=>['briefcase-business','Services'],'/customer/applications'=>['clipboard-list','Applications'],'/customer/documents'=>['folder-open','Documents'],'/customer/notifications'=>['bell','Notifications'],'/customer/billing'=>['receipt-indian-rupee','Billing'],'/customer/activity'=>['history','Activity']
] as $path=>$item):?><a class="<?=str_contains($uri,$path)?'active':''?>" href="<?=htmlspecialchars(url($path))?>"><i data-lucide="<?=$item[0]?>"></i><span><?=$item[1]?></span></a><?php endforeach;?></nav>
<form method="post" action="<?=htmlspecialchars(url('/customer/logout'))?>"><?=csrf_field()?><button><i data-lucide="log-out"></i> Sign out</button></form></aside>
<button class="portal-scrim" type="button" aria-label="Close navigation"></button>
<div class="portal-main"><header class="portal-topbar"><button class="portal-menu" type="button" aria-label="Open navigation"><i data-lucide="menu"></i></button><div><span>Partner workspace</span><h1><?=htmlspecialchars($title)?></h1></div>
<div class="portal-search" role="search"><i data-lucide="search"></i><input type="text" id="portalSearchInput" placeholder="Search applications, invoices, documents, notices…" autocomplete="off" aria-label="Search the customer portal"><div class="portal-search-results" id="portalSearchResults" hidden></div></div>
<a class="portal-avatar" href="<?=htmlspecialchars(url('/customer/profile'))?>"><?=htmlspecialchars(strtoupper(substr($customer['first_name'],0,1)))?></a></header>
<main class="portal-content"><?php if($m=\App\Core\Session::getFlash('success')):?><div class="portal-alert success"><?=htmlspecialchars($m)?></div><?php endif;?><?php if($m=\App\Core\Session::getFlash('error')):?><div class="portal-alert error"><?=htmlspecialchars($m)?></div><?php endif;?><?php if($m=\App\Core\Session::getFlash('info')):?><div class="portal-alert info"><?=htmlspecialchars($m)?></div><?php endif;?><?php require $content;?></main></div>
<div class="pay-disclaimer-overlay" id="payDisclaimerOverlay" hidden aria-hidden="true"><div class="pay-disclaimer-card" role="dialog" aria-modal="true" aria-labelledby="payDisclaimerTitle">
<h3 id="payDisclaimerTitle">Before you proceed</h3>
<ol>
<li>Subscription fees once paid are non-refundable and non-transferable.</li>
<li>Services shall be limited to the selected subscription model/package only.</li>
<li>The services under these subscription models are aimed at providing best possible information and guidance on various funding project opportunities to enable subscribers to take right decisions on offered project options for maximum possible chances of project sanction.</li>
<li>However, Udyam Ventures does not guarantee any project sanction, funding approval, empanelment, tender allocation, or business generation.</li>
<li>Proposal drafting, tender/RFP submission, DPR preparation, consultancy, execution support, and other project-specific services shall be charged separately.</li>
<li>Subscribers are solely responsible for eligibility, documentation, compliance, and timely submissions for any opportunity shared.</li>
<li>Udyam Ventures shall not be responsible for rejection, delay, modification, cancellation, or withdrawal of any project by the concerned authority.</li>
<li>Subscription content and materials are for subscriber use only and shall not be copied, shared, or misused.</li>
<li>No refund or compensation shall be applicable for non-utilization of services or missed opportunities.</li>
<li>Payment of subscription fees shall be treated as acceptance of all the above Terms &amp; Conditions.</li>
</ol>
<label class="pay-disclaimer-agree"><input type="checkbox" id="payDisclaimerAgree"><span>I have read and agree to all the Terms &amp; Conditions above.</span></label>
<div class="pay-disclaimer-actions"><button type="button" class="ghost-action" id="payDisclaimerCancel">Cancel</button><button type="button" class="primary-action" id="payDisclaimerContinue" disabled>Pay Now</button></div>
</div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }

    const body = document.body;
    const menu = document.querySelector('.portal-menu');
    const scrim = document.querySelector('.portal-scrim');
    const overlay = document.getElementById('payDisclaimerOverlay');
    const cancelButton = document.getElementById('payDisclaimerCancel');
    const continueButton = document.getElementById('payDisclaimerContinue');
    const agreeCheckbox = document.getElementById('payDisclaimerAgree');

    let pendingForm = null;

    function closeMenu() {
        body.classList.remove('portal-menu-open');
    }

    function openDisclaimer(form) {
        if (!overlay || !(form instanceof HTMLFormElement)) {
            console.error('Payment disclaimer or payment form is missing.');
            return;
        }

        pendingForm = form;
        if (agreeCheckbox) agreeCheckbox.checked = false;
        if (continueButton) continueButton.disabled = true;
        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        body.style.overflow = 'hidden';
    }

    function closeDisclaimer() {
        if (!overlay) {
            return;
        }

        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        body.style.overflow = '';
        pendingForm = null;
        if (agreeCheckbox) agreeCheckbox.checked = false;
        if (continueButton) continueButton.disabled = true;
    }

    agreeCheckbox?.addEventListener('change', function () {
        if (continueButton) continueButton.disabled = !agreeCheckbox.checked;
    });

    menu?.addEventListener('click', function () {
        body.classList.toggle('portal-menu-open');
    });

    scrim?.addEventListener('click', closeMenu);

    document.querySelectorAll('.portal-sidebar a').forEach(function (link) {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', function (event) {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const payButton = target.closest('.js-pay-now');

        if (!payButton) {
            return;
        }

        event.preventDefault();

        const form = payButton.closest('.js-pay-form');

        if (!form) {
            console.error('Pay Now form was not found.');
            return;
        }

        openDisclaimer(form);
    });

    cancelButton?.addEventListener('click', closeDisclaimer);

    overlay?.addEventListener('click', function (event) {
        if (event.target === overlay) {
            closeDisclaimer();
        }
    });

    continueButton?.addEventListener('click', function () {
        if (!agreeCheckbox?.checked) {
            return;
        }

        if (!pendingForm) {
            console.error('No payment form is selected.');
            return;
        }

        const form = pendingForm;

        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        body.style.overflow = '';
        pendingForm = null;

        HTMLFormElement.prototype.submit.call(form);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        closeMenu();

        if (overlay && !overlay.hidden) {
            closeDisclaimer();
        }
    });

    const searchInput = document.getElementById('portalSearchInput');
    const searchResults = document.getElementById('portalSearchResults');
    let searchTimer = null;
    let searchAbort = null;

    function renderSearchResults(results) {
        if (!searchResults) return;
        if (!results.length) {
            searchResults.innerHTML = '<div class="portal-search-empty">No matches yet. Try a different term.</div>';
            searchResults.hidden = false;
            return;
        }
        const esc = function (value) {
            const div = document.createElement('div');
            div.textContent = String(value == null ? '' : value);
            return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        };
        const groups = {};
        results.forEach(function (item) {
            groups[item.group] = groups[item.group] || [];
            groups[item.group].push(item);
        });
        let html = '';
        Object.keys(groups).forEach(function (group) {
            html += '<div class="portal-search-group">' + esc(group) + '</div>';
            groups[group].forEach(function (item) {
                html += '<a class="portal-search-item" href="' + esc(item.url) + '"><i data-lucide="' + esc(item.icon) + '"></i><span><strong>' + esc(item.title) + '</strong><small>' + esc(item.meta) + '</small></span></a>';
            });
        });
        searchResults.innerHTML = html;
        searchResults.hidden = false;
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    searchInput?.addEventListener('input', function () {
        const value = searchInput.value.trim();
        clearTimeout(searchTimer);
        if (value.length < 2) {
            if (searchResults) searchResults.hidden = true;
            return;
        }
        searchTimer = setTimeout(function () {
            if (searchAbort) searchAbort.abort();
            searchAbort = new AbortController();
            fetch('<?=htmlspecialchars(url('/customer/search'))?>?q=' + encodeURIComponent(value), { signal: searchAbort.signal })
                .then(function (res) { return res.json(); })
                .then(function (data) { renderSearchResults(data.results || []); })
                .catch(function (err) {
                    if (err.name === 'AbortError') return;
                    console.error(err);
                    if (searchResults) {
                        searchResults.innerHTML = '<div class="portal-search-empty">Search is temporarily unavailable. Please try again.</div>';
                        searchResults.hidden = false;
                    }
                });
        }, 250);
    });

    searchInput?.addEventListener('focus', function () {
        if (searchInput.value.trim().length >= 2 && searchResults && searchResults.innerHTML) {
            searchResults.hidden = false;
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchResults || !searchInput) return;
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.hidden = true;
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && searchResults) {
            searchResults.hidden = true;
        }
    });
});
</script>
</body></html>