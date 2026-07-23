<?php $totalPages = max(1, (int) ceil($total / $perPage)); ?>
<div class="page-header">
    <div>
        <h2><?= htmlspecialchars($definition['title']) ?></h2>
        <p>Manage <?= htmlspecialchars(strtolower($definition['title'])) ?>.</p>
    </div>
    <a class="btn btn-primary" href="<?= htmlspecialchars(url('/admin/' . $module . '/create')) ?>">
        <i data-lucide="plus"></i> Add <?= htmlspecialchars($definition['singular']) ?>
    </a>
</div>

<div class="card">
    <form method="get" action="<?= htmlspecialchars(url('/admin/' . $module)) ?>" class="filters-bar">
        <input class="form-control" type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search records…">
        <button class="btn btn-secondary" type="submit">Search</button>
    </form>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Title</th><th>Status</th><th>Order</th><th>Updated</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($records === []): ?>
                <tr><td colspan="5">No records found.</td></tr>
            <?php else: foreach ($records as $record): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $record['title']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars(ucfirst((string) $record['status'])) ?></span></td>
                    <td><?= (int) $record['sort_order'] ?></td>
                    <td><?= htmlspecialchars((string) $record['updated_at']) ?></td>
                    <td>
                        <a class="btn btn-sm" href="<?= htmlspecialchars(url('/admin/' . $module . '/edit/' . $record['id'])) ?>">Edit</a>
                        <form method="post" action="<?= htmlspecialchars(url('/admin/' . $module . '/delete/' . $record['id'])) ?>" class="d-inline" onsubmit="return confirm('Delete this record?')">
                            <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</div>
