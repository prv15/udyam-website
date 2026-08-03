<div class="auth-heading"><span>Account recovery</span><h2>Forgot your password?</h2><p>We will send a secure, time-limited reset link.</p></div>
<form method="post" action="<?=htmlspecialchars(url('/customer/forgot-password'))?>"><?=csrf_field()?>
<label>Email address</label><div class="field-icon"><i data-lucide="mail"></i><input type="email" name="email" required></div>
<button class="primary-action" type="submit">Send reset link <i data-lucide="send"></i></button><p class="auth-switch"><a href="<?=htmlspecialchars(url('/customer/login'))?>">Back to sign in</a></p></form>
