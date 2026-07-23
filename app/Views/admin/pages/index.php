<?php $totalPages = max(1, (int) ceil($total / $perPage)); ?>
<div class="page-header">
    <div><h1>Pages</h1><p>Create and manage website pages.</p></div>
    <a href="<?= htmlspecialchars(url('/admin/pages/create')) ?>" class="btn btn-primary"><i data-lucide="plus"></i> New Page</a>
</div>

<div class="card">
    <form class="filters-bar" method="get" action="<?= htmlspecialchars(url('/admin/pages')) ?>">
        <input class="form-control" type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search title or slug…">
        <select class="form-control" name="status">
            <option value="">All statuses</option>
            <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        <?php if ($search !== '' || $status !== ''): ?><a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/pages')) ?>">Clear</a><?php endif; ?>
    </form>

    <div class="table-responsive"><table class="table custom-table">
        <thead><tr><th>Page</th><th>Status</th><th>Author</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if ($pages === []): ?>
            <tr><td colspan="5">No pages found. Create your first website page.</td></tr>
        <?php else: foreach ($pages as $page): ?>
            <tr>
                <td><strong><?= htmlspecialchars($page['title']) ?></strong><br><small>/<?= htmlspecialchars($page['slug']) ?></small></td>
                <td><span class="badge <?= $page['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars(ucfirst($page['status'])) ?></span></td>
                <td><?= htmlspecialchars($page['author'] ?: 'System') ?></td>
                <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($page['updated_at']))) ?></td>
                <td>
                    <a class="btn btn-sm" href="<?= htmlspecialchars(url('/admin/pages/edit/' . $page['id'])) ?>">Edit</a>
                    <a class="btn btn-sm" target="_blank" href="<?= htmlspecialchars(url('/admin/pages/preview/' . $page['id'])) ?>">Preview</a>
                    <?php if ($page['status'] === 'published'): ?><a class="btn btn-sm" target="_blank" href="<?= htmlspecialchars(url('/' . $page['slug'])) ?>">View</a><?php endif; ?>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/pages/delete/' . $page['id'])) ?>" class="d-inline" onsubmit="return confirm('Move this page to trash?')">
                        <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>

    <?php if ($totalPages > 1): ?><nav class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= $i === $currentPage ? 'active' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav><?php endif; ?>
</div>
