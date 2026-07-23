<div class="page-header">
    <div><h2>Users & Roles</h2><p>Manage administrator and customer accounts.</p></div>
    <a class="btn btn-primary" href="<?= htmlspecialchars(url('/admin/users/create')) ?>">Add User</a>
</div>
<div class="card">
    <form method="get" class="filters-bar"><input class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search users…"><button class="btn" type="submit">Search</button></form>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody><?php if ($users === []): ?><tr><td colspan="5">No users found.</td></tr><?php else: foreach ($users as $item): ?><tr>
            <td><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></td>
            <td><?= htmlspecialchars($item['email']) ?></td><td><?= htmlspecialchars(ucfirst($item['user_type'])) ?></td>
            <td><?= htmlspecialchars(ucfirst($item['status'])) ?></td><td>
                <a class="btn btn-sm" href="<?= htmlspecialchars(url('/admin/users/edit/' . $item['id'])) ?>">Edit</a>
                <form class="d-inline" method="post" action="<?= htmlspecialchars(url('/admin/users/delete/' . $item['id'])) ?>" onsubmit="return confirm('Delete this user?')"><?= csrf_field() ?><button class="btn btn-sm btn-danger">Delete</button></form>
            </td>
        </tr><?php endforeach; endif; ?></tbody>
    </table></div>
</div>
