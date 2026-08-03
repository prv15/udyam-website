<div class="auth-heading"><span>Customer portal</span><h2>Welcome back</h2><p>Sign in to manage your Udyam relationship.</p></div>
<form method="post" action="<?=htmlspecialchars(url('/customer/login'))?>"><?=csrf_field()?>
<label>Email address</label><div class="field-icon"><i data-lucide="mail"></i><input type="email" name="email" autocomplete="email" required></div>
<label>Password</label><div class="field-icon"><i data-lucide="lock-keyhole"></i><input type="password" name="password" autocomplete="current-password" required></div>
<div class="auth-links"><a href="<?=htmlspecialchars(url('/customer/forgot-password'))?>">Forgot password?</a></div>
<button class="primary-action" type="submit">Sign in <i data-lucide="arrow-right"></i></button>
<p class="auth-switch">New to Udyam? <a href="<?=htmlspecialchars(url('/customer/register'))?>">Create an account</a></p></form>
