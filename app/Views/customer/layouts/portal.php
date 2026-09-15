<?php $cssVersion=(string)(@filemtime(ASSET_PATH.'/customer/portal.css')?:'1'); $uri=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)?:''; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="<?=htmlspecialchars(csrf_token())?>"><title><?=htmlspecialchars($title)?> | Udyam Partner Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=htmlspecialchars(url('/assets/customer/portal.css'))?>?v=<?=rawurlencode($cssVersion)?>"><link rel="stylesheet" href="<?=htmlspecialchars(url('/assets/customer/portal-erp.css'))?>?v=<?=rawurlencode((string)(@filemtime(ASSET_PATH.'/customer/portal-erp.css')?:'1'))?>"><script src="https://unpkg.com/lucide@latest"></script></head><body class="portal-body">
<aside class="portal-sidebar"><a class="portal-brand" href="<?=htmlspecialchars(url('/customer/dashboard'))?>"><img src="<?=htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png'))?>" alt="Udyam Ventures"></a>
<nav><a class="<?=str_contains($uri,'/customer/dashboard')?'active':''?>" href="<?=htmlspecialchars(url('/customer/dashboard'))?>"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a><a class="<?=str_contains($uri,'/customer/profile')?'active':''?>" href="<?=htmlspecialchars(url('/customer/profile'))?>"><i data-lucide="building-2"></i><span>Company Profile</span></a><details class="portal-nav-group"<?= str_contains($uri,'/customer/subscription') || str_contains($uri,'/customer/plans') ? ' open' : '' ?>><summary><i data-lucide="calendar-check"></i><span>Subscriptions</span><i data-lucide="chevron-down"></i></summary><div><a class="<?=str_contains($uri,'/customer/subscription')?'active':''?>" href="<?=htmlspecialchars(url('/customer/subscription'))?>">My Subscription</a><a class="<?=str_contains($uri,'/customer/plans')?'active':''?>" href="<?=htmlspecialchars(url('/customer/plans'))?>">View Plans</a></div></details><a class="<?=str_contains($uri,'/customer/tenders')?'active':''?>" href="<?=htmlspecialchars(url('/customer/tenders'))?>"><i data-lucide="megaphone"></i><span>Notices &amp; Tenders</span></a><a class="<?=str_contains($uri,'/customer/services')?'active':''?>" href="<?=htmlspecialchars(url('/customer/services'))?>"><i data-lucide="briefcase-business"></i><span>Services</span></a><a class="<?=str_contains($uri,'/customer/applications')?'active':''?>" href="<?=htmlspecialchars(url('/customer/applications'))?>"><i data-lucide="clipboard-list"></i><span>Applications</span></a><a class="<?=str_contains($uri,'/customer/documents')?'active':''?>" href="<?=htmlspecialchars(url('/customer/documents'))?>"><i data-lucide="folder-open"></i><span>Documents</span></a><a class="<?=str_contains($uri,'/customer/notifications')?'active':''?>" href="<?=htmlspecialchars(url('/customer/notifications'))?>"><i data-lucide="bell"></i><span>Notifications</span></a><details class="portal-nav-group"<?= str_contains($uri,'/customer/billing') ? ' open' : '' ?>><summary><i data-lucide="receipt-text"></i><span>Billing</span><i data-lucide="chevron-down"></i></summary><div><a class="<?= $uri==='/customer/billing'?'active':''?>" href="<?=htmlspecialchars(url('/customer/billing'))?>">Billing Overview</a><a class="<?=str_contains($uri,'/customer/billing/invoices')?'active':''?>" href="<?=htmlspecialchars(url('/customer/billing/invoices'))?>">My Invoices</a><a class="<?=str_contains($uri,'/customer/billing/payments')?'active':''?>" href="<?=htmlspecialchars(url('/customer/billing/payments'))?>">Payments &amp; Receipts</a></div></details><a class="<?=str_contains($uri,'/customer/support')?'active':''?>" href="<?=htmlspecialchars(url('/customer/support'))?>"><i data-lucide="headset"></i><span>Help &amp; Support</span></a><a class="<?=str_contains($uri,'/customer/activity')?'active':''?>" href="<?=htmlspecialchars(url('/customer/activity'))?>"><i data-lucide="history"></i><span>Activity</span></a></nav>
<form method="post" action="<?=htmlspecialchars(url('/customer/logout'))?>"><?=csrf_field()?><button><i data-lucide="log-out"></i> Sign out</button></form></aside>
<button class="portal-scrim" type="button" aria-label="Close navigation"></button>
<div class="portal-main"><header class="portal-topbar"><button class="portal-menu" type="button" aria-label="Open navigation"><i data-lucide="menu"></i></button><div><span>Partner workspace</span><h1><?=htmlspecialchars($title)?></h1></div>
<div class="portal-search" role="search" data-search-url="<?=htmlspecialchars(url('/customer/search'))?>"><i data-lucide="search"></i><input type="text" id="portalSearchInput" placeholder="Search services, applications, invoices…" autocomplete="off" aria-label="Search your portal" role="combobox" aria-expanded="false" aria-controls="portalSearchResults" aria-autocomplete="list"><kbd>⌘ K</kbd><div class="portal-search-results" id="portalSearchResults" role="listbox" hidden></div></div>
<div class="portal-notification-menu" data-feed-url="<?=htmlspecialchars(url('/customer/notifications/feed'))?>"><button type="button" class="portal-notification-button" aria-label="Notifications" aria-expanded="false"><i data-lucide="bell"></i><b hidden>0</b></button><div class="portal-notification-dropdown" hidden><div><strong>Notifications</strong><a href="<?=htmlspecialchars(url('/customer/notifications'))?>">View all</a></div><section></section></div></div><a class="portal-avatar" href="<?=htmlspecialchars(url('/customer/profile'))?>"><?=htmlspecialchars(strtoupper(substr($customer['first_name'],0,1)))?></a></header>
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

        const accepted = form.querySelector('input[name="disclaimer_accepted"]');
        if (accepted) accepted.value = '1';
        continueButton.disabled = true;
        continueButton.textContent = 'Opening secure payment…';

        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        body.style.overflow = '';
        pendingForm = null;

        form.requestSubmit();
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
    const searchBox = document.querySelector('.portal-search');
    let searchTimer = null;
    let searchAbort = null;
    let activeSearchIndex = -1;

    function closeSearch() {
        if (!searchResults || !searchInput) return;
        searchResults.hidden = true;
        searchInput.setAttribute('aria-expanded', 'false');
        activeSearchIndex = -1;
    }

    function openSearch() {
        if (!searchResults || !searchInput) return;
        searchResults.hidden = false;
        searchInput.setAttribute('aria-expanded', 'true');
    }

    function searchState(icon, title, message) {
        if (!searchResults) return;
        searchResults.replaceChildren();
        const state = document.createElement('div');
        state.className = 'portal-search-state';
        const iconNode = document.createElement('i');
        iconNode.setAttribute('data-lucide', icon);
        const copy = document.createElement('span');
        const heading = document.createElement('strong');
        const description = document.createElement('small');
        heading.textContent = title;
        description.textContent = message;
        copy.append(heading, description);
        state.append(iconNode, copy);
        searchResults.append(state);
        openSearch();
        if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function renderSearchResults(results, query) {
        if (!searchResults) return;
        searchResults.replaceChildren();
        if (!results.length) {
            searchState('search-x', 'No matches found', 'Try another phrase for “' + query + '”.');
            return;
        }
        const groups = new Map();
        results.forEach(function (item) {
            if (!groups.has(item.group)) groups.set(item.group, []);
            groups.get(item.group).push(item);
        });
        groups.forEach(function (items, group) {
            const section = document.createElement('section');
            const groupTitle = document.createElement('div');
            groupTitle.className = 'portal-search-group';
            groupTitle.textContent = group;
            section.append(groupTitle);
            items.forEach(function (item) {
                const link = document.createElement('a');
                link.className = 'portal-search-item';
                link.href = item.url;
                link.setAttribute('role', 'option');
                const icon = document.createElement('i');
                icon.setAttribute('data-lucide', item.icon || 'search');
                const copy = document.createElement('span');
                const title = document.createElement('strong');
                const meta = document.createElement('small');
                title.textContent = item.title;
                meta.textContent = item.meta;
                copy.append(title, meta);
                const arrow = document.createElement('i');
                arrow.className = 'portal-search-arrow';
                arrow.setAttribute('data-lucide', 'arrow-up-right');
                link.append(icon, copy, arrow);
                section.append(link);
            });
            searchResults.append(section);
        });
        openSearch();
        if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function runSearch() {
        const value = searchInput?.value.trim() || '';
        if (value.length < 2) {
            if (value.length === 1) searchState('command', 'Keep typing', 'Enter at least two characters to search your workspace.');
            else closeSearch();
            return;
        }
        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();
        searchState('loader-circle', 'Searching your workspace', 'Looking through your services, billing and documents…');
        fetch((searchBox?.dataset.searchUrl || '<?=htmlspecialchars(url('/customer/search'))?>') + '?q=' + encodeURIComponent(value), {
            headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: searchAbort.signal
        })
            .then(function (res) { if (!res.ok) throw new Error('Search failed'); return res.json(); })
            .then(function (data) { renderSearchResults(data.results || [], value); })
            .catch(function (err) { if (err.name !== 'AbortError') searchState('wifi-off', 'Search is unavailable', 'Please try again in a moment.'); });
    }

    searchInput?.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 220);
    });

    searchInput?.addEventListener('focus', function () {
        if (searchInput.value.trim().length >= 2 && searchResults && searchResults.children.length) {
            openSearch();
        }
    });

    searchInput?.addEventListener('keydown', function (event) {
        const links = [...(searchResults?.querySelectorAll('.portal-search-item') || [])];
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!links.length) return;
            activeSearchIndex = event.key === 'ArrowDown'
                ? (activeSearchIndex + 1) % links.length
                : (activeSearchIndex - 1 + links.length) % links.length;
            links.forEach(function (link, index) { link.classList.toggle('is-active', index === activeSearchIndex); });
            links[activeSearchIndex].scrollIntoView({block: 'nearest'});
        } else if (event.key === 'Enter' && activeSearchIndex >= 0 && links[activeSearchIndex]) {
            window.location.href = links[activeSearchIndex].href;
        } else if (event.key === 'Escape') {
            closeSearch();
            searchInput.blur();
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchResults || !searchInput) return;
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            closeSearch();
        }
    });

    document.addEventListener('keydown', function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k' && searchInput) {
            event.preventDefault();
            searchInput.focus();
            searchInput.select();
        } else if (event.key === 'Escape' && searchResults) {
            closeSearch();
        }
    });
});
</script>
<script>document.addEventListener('DOMContentLoaded',function(){const menu=document.querySelector('.portal-notification-menu'),button=menu?.querySelector('button'),drop=menu?.querySelector('.portal-notification-dropdown'),list=drop?.querySelector('section');if(!menu||!button||!drop||!list)return;const render=function(items){list.innerHTML=items.length?items.map(function(item){return '<a class="'+(item.read_at?'':'unread')+'" href="'+(item.action_url||'<?=htmlspecialchars(url('/customer/notifications'))?>')+'"><i data-lucide="bell-ring"></i><span><strong>'+String(item.title||'').replace(/[&<>]/g,'')+'</strong><small>'+String(item.message||'').replace(/[&<>]/g,'')+'</small></span></a>';}).join(''):'<p>You are all caught up.</p>';window.lucide&&lucide.createIcons({nodes:[list]});};const load=function(){fetch(menu.dataset.feedUrl,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json();}).then(function(data){const count=Number(data.count||0),badge=button.querySelector('b');badge.hidden=!count;badge.textContent=Math.min(99,count);render(data.notifications||[]);}).catch(function(){});};button.addEventListener('click',function(e){e.stopPropagation();drop.hidden=!drop.hidden;button.setAttribute('aria-expanded',String(!drop.hidden));if(!drop.hidden)load();});document.addEventListener('click',function(e){if(!menu.contains(e.target))drop.hidden=true;});load();setInterval(load,45000);});</script>
</body></html>
