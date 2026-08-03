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
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeSidebar();
    });

    initialiseCreateMenu();
    initialiseGlobalSearch();

    if (typeof Chart !== "undefined" && document.querySelector(".advanced-dashboard")) {
        const script = document.createElement("script");
        script.src = new URL("dashboard.js", document.currentScript?.src || window.location.href).href;
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
