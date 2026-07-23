<div class="page-header"><div><h2>My Profile</h2><p>Update your administrator details.</p></div></div>
<form method="post" action="<?= htmlspecialchars(url('/admin/profile/update')) ?>"><?= csrf_field() ?>
<div class="card"><div class="card-body">
<div class="form-group"><label>First Name *</label><input class="form-control" name="first_name" value="<?= htmlspecialchars($record['first_name']) ?>" required></div>
<div class="form-group"><label>Last Name</label><input class="form-control" name="last_name" value="<?= htmlspecialchars($record['last_name']) ?>"></div>
<div class="form-group"><label>Email *</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($record['email']) ?>" required></div>
<button class="btn btn-primary">Update Profile</button>
</div></div></form>
