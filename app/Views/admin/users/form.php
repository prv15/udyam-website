<?php
$errors = \App\Core\Session::get('errors', []); $old = \App\Core\Session::get('old', []);
\App\Core\Session::forget('errors'); \App\Core\Session::forget('old');
$values = array_merge($record, $old); $edit = $mode === 'edit';
?>
<div class="page-header"><h2><?= htmlspecialchars($title) ?></h2><a class="btn" href="<?= htmlspecialchars(url('/admin/users')) ?>">Back</a></div>
<form method="post" action="<?= htmlspecialchars(url($edit ? '/admin/users/update/' . $record['id'] : '/admin/users/store')) ?>">
<?= csrf_field() ?><div class="card"><div class="card-body">
<?php foreach (['first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email'] as $name => $label): ?>
<div class="form-group"><label><?= $label ?><?= $name !== 'last_name' ? ' *' : '' ?></label><input class="form-control" type="<?= $name === 'email' ? 'email' : 'text' ?>" name="<?= $name ?>" value="<?= htmlspecialchars((string) ($values[$name] ?? '')) ?>"><?php if (isset($errors[$name][0])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($errors[$name][0]) ?></div><?php endif; ?></div>
<?php endforeach; ?>
<div class="form-group"><label>Password<?= $edit ? ' (leave blank to keep current)' : ' *' ?></label><input class="form-control" type="password" name="password"><?php if (isset($errors['password'][0])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($errors['password'][0]) ?></div><?php endif; ?></div>
<div class="form-group"><label>Role</label><select class="form-control" name="user_type"><option value="admin" <?= ($values['user_type'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option><option value="customer" <?= ($values['user_type'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option></select></div>
<div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="active" <?= ($values['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($values['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
<button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Create' ?> User</button>
</div></div></form>
