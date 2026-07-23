<div class="page-header"><div><h2>Change Password</h2><p>Use at least 8 characters.</p></div></div>
<form method="post" action="<?= htmlspecialchars(url('/admin/change-password/update')) ?>"><?= csrf_field() ?>
<div class="card"><div class="card-body">
<div class="form-group"><label>Current Password</label><input class="form-control" type="password" name="current_password" required></div>
<div class="form-group"><label>New Password</label><input class="form-control" type="password" name="password" minlength="8" required></div>
<div class="form-group"><label>Confirm New Password</label><input class="form-control" type="password" name="password_confirmation" minlength="8" required></div>
<button class="btn btn-primary">Change Password</button>
</div></div></form>
