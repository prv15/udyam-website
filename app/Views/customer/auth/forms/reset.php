<div class="auth-heading"><span>Secure reset</span><h2>Choose a new password</h2><p>Use at least eight characters.</p></div>
<form method="post" action="<?=htmlspecialchars(url('/customer/reset-password'))?>"><?=csrf_field()?><input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
<label>New password</label><input type="password" name="password" required><label>Confirm password</label><input type="password" name="password_confirmation" required>
<button class="primary-action" type="submit">Update password <i data-lucide="shield-check"></i></button></form>
