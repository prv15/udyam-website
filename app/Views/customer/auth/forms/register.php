<div class="auth-heading"><span>Customer registration</span><h2>Create your workspace</h2><p>Start managing services and billing in one secure account.</p></div>
<form method="post" action="<?=htmlspecialchars(url('/customer/register'))?>"><?=csrf_field()?>
<div class="auth-grid"><div><label>Full name</label><input name="full_name" value="<?=htmlspecialchars($old['full_name']??'')?>" required></div><div><label>Company name</label><input name="company_name" value="<?=htmlspecialchars($old['company_name']??'')?>"></div>
<div><label>Mobile</label><input name="mobile" value="<?=htmlspecialchars($old['mobile']??'')?>" required></div><div><label>Email</label><input type="email" name="email" value="<?=htmlspecialchars($old['email']??'')?>" required></div>
<div><label>Password</label><input type="password" name="password" required></div><div><label>Confirm password</label><input type="password" name="password_confirmation" required></div></div>
<button class="primary-action" type="submit">Create secure account <i data-lucide="arrow-right"></i></button>
<p class="auth-switch">Already registered? <a href="<?=htmlspecialchars(url('/customer/login'))?>">Sign in</a></p></form>
