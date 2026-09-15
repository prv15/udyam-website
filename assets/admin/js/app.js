document.addEventListener("DOMContentLoaded", () => {
    if (window.lucide) {
        lucide.createIcons();
    }

    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebarClose = document.getElementById("sidebarMobileClose");
    const sidebarBackdrop = document.getElementById("sidebarBackdrop");
    const mobileSidebar = () => window.matchMedia("(max-width: 1024px)").matches;
    const closeSidebar = () => document.body.classList.remove("sidebar-open");

    sidebarToggle?.addEventListener("click", () => {
        if (mobileSidebar()) {
            document.body.classList.toggle("sidebar-open");
        } else {
            document.body.classList.toggle("sidebar-collapsed");
        }
    });
    sidebarClose?.addEventListener("click", closeSidebar);
    sidebarBackdrop?.addEventListener("click", closeSidebar);
    document.querySelectorAll(".sidebar .menu-item").forEach((item) => {
        item.addEventListener("click", () => {
            if (mobileSidebar()) closeSidebar();
        });
    });
    window.addEventListener("resize", () => {
        if (!mobileSidebar()) closeSidebar();
    });
    const sidebarScroller = document.querySelector('.sidebar-menu');
    const sidebarScrollKey = 'udyam.admin.sidebar.scroll';
    if (sidebarScroller) {
        const savedScroll = Number(sessionStorage.getItem(sidebarScrollKey) || 0);
        if (savedScroll) sidebarScroller.scrollTop = savedScroll;
        sidebarScroller.addEventListener('scroll', () => sessionStorage.setItem(sidebarScrollKey, String(sidebarScroller.scrollTop)), {passive: true});
        document.querySelectorAll('.sidebar a.menu-item,.sidebar-submenu a').forEach((item) => item.addEventListener('click', () => sessionStorage.setItem(sidebarScrollKey, String(sidebarScroller.scrollTop))));
    }
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeSidebar();
    });

    initialiseCreateMenu();
    initialiseGlobalSearch();
    initialiseAdminNotifications();

    if (typeof Chart !== "undefined" && document.querySelector(".advanced-dashboard")) {
        const script = document.createElement("script");
        script.src = window.udyamAdminAssets?.dashboard || "/assets/admin/js/dashboard.js";
        document.body.appendChild(script);
    }
});

function initialiseCreateMenu() {
    const wrapper = document.querySelector(".create-menu");
    const trigger = wrapper?.querySelector(".create-menu-trigger");
    const panel = wrapper?.querySelector(".create-menu-panel");
    if (!wrapper || !trigger || !panel) return;

    const setOpen = (open) => {
        panel.hidden = !open;
        trigger.setAttribute("aria-expanded", String(open));
        wrapper.classList.toggle("is-open", open);
    };

    trigger.addEventListener("click", (event) => {
        event.stopPropagation();
        setOpen(panel.hidden);
    });
    document.addEventListener("click", (event) => {
        if (!wrapper.contains(event.target)) setOpen(false);
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") setOpen(false);
    });
}

function initialiseAdminNotifications() {
    const wrapper = document.querySelector('.admin-notification-menu');
    const trigger = wrapper?.querySelector('.notification-button');
    const panel = wrapper?.querySelector('.admin-notification-dropdown');
    const list = wrapper?.querySelector('.admin-notification-list');
    const count = wrapper?.querySelector('.notification-count');
    if (!wrapper || !trigger || !panel || !list) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const request = async (url) => fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}}).then((response) => response.ok ? response.json() : Promise.reject());
    const setCount = (value) => {
        const amount = Number(value || 0);
        count.hidden = amount < 1;
        count.textContent = Math.min(99, amount);
        trigger.classList.toggle('has-notifications', amount > 0);
        trigger.setAttribute('aria-label', `${amount} unread notifications`);
    };
    const escape = (value) => String(value || '').replace(/[&<>"]/g, (character) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[character]));
    const timeAgo = (value) => {
        const seconds = Math.max(0, Math.floor((Date.now() - new Date(String(value).replace(' ', 'T')).getTime()) / 1000));
        if (seconds < 60) return 'Just now'; if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} hr ago`; if (seconds < 172800) return 'Yesterday';
        return `${Math.floor(seconds / 86400)} days ago`;
    };
    const render = (items) => {
        if (!items.length) { list.innerHTML = '<div class="admin-notification-empty">You are all caught up.</div>'; return; }
        list.innerHTML = items.map((item) => `<a class="admin-notification-item ${item.read_at ? '' : 'unread'}" href="${escape(item.action_url || '/admin/notification-center')}" data-notification-id="${Number(item.id)}"><span><i data-lucide="bell-ring"></i></span><div><strong>${escape(item.title)}</strong><small>${escape(item.message)}</small><em>${timeAgo(item.created_at)}</em></div></a>`).join('');
        if (window.lucide) lucide.createIcons({nodes: [list]});
    };
    const load = async () => { try { const payload = await request(wrapper.dataset.notificationUrl); setCount(payload.count); render(payload.notifications || []); } catch (_) {} };
    const post = async (url) => fetch(url, {method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body: `_token=${encodeURIComponent(token)}`}).then((response) => response.ok ? response.json() : Promise.reject());
    trigger.addEventListener('click', async (event) => { event.stopPropagation(); panel.hidden = !panel.hidden; trigger.setAttribute('aria-expanded', String(!panel.hidden)); if (!panel.hidden) await load(); });
    const readUrl = (id) => (wrapper.dataset.notificationReadUrl || '').replace('__id__', id);
    list.addEventListener('click', (event) => { const item = event.target.closest('[data-notification-id]'); if (!item) return; post(readUrl(item.dataset.notificationId)).then((payload) => setCount(payload.count)).catch(() => {}); });
    wrapper.querySelector('[data-admin-notification-read-all]')?.addEventListener('click', () => post(wrapper.dataset.notificationReadAllUrl).then((payload) => { setCount(payload.count); load(); }).catch(() => {}));
    const notificationCenter = document.querySelector('[data-notification-center]');
    document.querySelectorAll('[data-notification-read-all]').forEach((button) => button.addEventListener('click', () => post(notificationCenter?.dataset.readAllUrl || wrapper.dataset.notificationReadAllUrl).then(() => window.location.reload()).catch(() => {})));
    document.querySelectorAll('[data-notification-delete]').forEach((button) => button.addEventListener('click', () => { const row = button.closest('[data-notification-id]'); if (!row) return; post(readUrl(row.dataset.notificationId).replace('/read', '/delete')).then(() => row.remove()).catch(() => {}); }));
    document.addEventListener('click', (event) => { if (!wrapper.contains(event.target)) { panel.hidden = true; trigger.setAttribute('aria-expanded', 'false'); } });
    setInterval(load, 45000);
}

function initialiseGlobalSearch() {
    const wrapper = document.querySelector(".global-search");
    const input = wrapper?.querySelector("#adminGlobalSearch");
    const results = wrapper?.querySelector(".global-search-results");
    if (!wrapper || !input || !results) return;

    const endpoint = wrapper.dataset.searchUrl;
    let timer;
    let controller;
    let activeIndex = -1;

    const close = () => {
        results.hidden = true;
        input.setAttribute("aria-expanded", "false");
        activeIndex = -1;
    };

    const open = () => {
        results.hidden = false;
        input.setAttribute("aria-expanded", "true");
    };

    const setMessage = (icon, title, description) => {
        results.replaceChildren();
        const empty = document.createElement("div");
        empty.className = "search-state";
        const iconNode = document.createElement("i");
        iconNode.setAttribute("data-lucide", icon);
        const copy = document.createElement("span");
        const strong = document.createElement("strong");
        strong.textContent = title;
        const small = document.createElement("small");
        small.textContent = description;
        copy.append(strong, small);
        empty.append(iconNode, copy);
        results.append(empty);
        open();
        lucide.createIcons({ nodes: [results] });
    };

    const render = (items, query) => {
        results.replaceChildren();
        if (!items.length) {
            setMessage("search-x", "No matching records", `Try another phrase for “${query}”.`);
            return;
        }

        const groups = new Map();
        items.forEach((item) => {
            if (!groups.has(item.group)) groups.set(item.group, []);
            groups.get(item.group).push(item);
        });

        groups.forEach((groupItems, groupName) => {
            const section = document.createElement("section");
            const heading = document.createElement("div");
            heading.className = "search-result-heading";
            heading.textContent = groupName;
            section.append(heading);

            groupItems.forEach((item) => {
                const link = document.createElement("a");
                link.href = item.url;
                link.setAttribute("role", "option");
                link.className = "search-result-item";

                const iconWrap = document.createElement("span");
                iconWrap.className = "search-result-icon";
                const icon = document.createElement("i");
                icon.setAttribute("data-lucide", item.icon || "search");
                iconWrap.append(icon);

                const copy = document.createElement("span");
                const title = document.createElement("strong");
                title.textContent = item.title;
                const meta = document.createElement("small");
                meta.textContent = `${item.type} · ${item.meta}`;
                copy.append(title, meta);

                const arrow = document.createElement("i");
                arrow.setAttribute("data-lucide", "arrow-up-right");
                arrow.className = "search-result-arrow";
                link.append(iconWrap, copy, arrow);
                section.append(link);
            });
            results.append(section);
        });
        open();
        lucide.createIcons({ nodes: [results] });
    };

    const runSearch = async () => {
        const query = input.value.trim();
        if (query.length < 2) {
            query.length === 1
                ? setMessage("command", "Keep typing", "Enter at least two characters to search the CMS.")
                : close();
            return;
        }

        controller?.abort();
        controller = new AbortController();
        setMessage("loader-circle", "Searching Udyam CMS", "Looking across customers, billing and website content…");

        try {
            const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                signal: controller.signal
            });
            if (!response.ok) throw new Error("Search failed");
            const payload = await response.json();
            render(Array.isArray(payload.results) ? payload.results : [], query);
        } catch (error) {
            if (error.name !== "AbortError") {
                setMessage("wifi-off", "Search is unavailable", "Please try again in a moment.");
            }
        }
    };

    input.addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(runSearch, 220);
    });
    input.addEventListener("focus", () => {
        if (input.value.trim().length >= 2 && results.children.length) open();
    });
    input.addEventListener("keydown", (event) => {
        const links = [...results.querySelectorAll(".search-result-item")];
        if (event.key === "ArrowDown" || event.key === "ArrowUp") {
            event.preventDefault();
            if (!links.length) return;
            activeIndex = event.key === "ArrowDown"
                ? (activeIndex + 1) % links.length
                : (activeIndex - 1 + links.length) % links.length;
            links.forEach((link, index) => link.classList.toggle("is-active", index === activeIndex));
            links[activeIndex].scrollIntoView({ block: "nearest" });
        } else if (event.key === "Enter" && activeIndex >= 0 && links[activeIndex]) {
            window.location.href = links[activeIndex].href;
        } else if (event.key === "Escape") {
            close();
            input.blur();
        }
    });
    document.addEventListener("click", (event) => {
        if (!wrapper.contains(event.target)) close();
    });
    document.addEventListener("keydown", (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
            event.preventDefault();
            input.focus();
            input.select();
        }
    });
}
