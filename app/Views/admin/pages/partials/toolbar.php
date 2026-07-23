<div class="page-toolbar">

    <div class="toolbar-left">

        <div class="search-input">
            <i data-lucide="search"></i>

            <input
                type="text"
                class="table-search"
                placeholder="Search pages..."
            >
        </div>

        <select class="table-filter">
            <option>All Status</option>
            <option>Published</option>
            <option>Draft</option>
            <option>Archived</option>
        </select>

    </div>

    <div class="toolbar-right">

        <button class="btn btn-light">
            <i data-lucide="download"></i>
            Export
        </button>

        <a href="<?= htmlspecialchars(url('/admin/pages/create'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
            <i data-lucide="plus"></i>
            New Page
        </a>

    </div>

</div>
