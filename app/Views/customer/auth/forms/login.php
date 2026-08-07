<?php $unverifiedEmail = \App\Core\Session::getFlash('unverified_email'); ?>
<div class="auth-heading"><span>Partner portal</span><h2>Welcome back</h2><p>Sign in to manage your Udyam relationship.</p></div>
<form method="post" action="<?=htmlspecialchars(url('/customer/login'))?>"><?=csrf_field()?>
<label>Email address</label><div class="field-icon"><i data-lucide="mail"></i><input type="email" name="email" autocomplete="email" required></div>
<label>Password</label><div class="field-icon"><i data-lucide="lock-keyhole"></i><input type="password" name="password" autocomplete="current-password" required></div>
<div class="auth-links"><a href="<?=htmlspecialchars(url('/customer/forgot-password'))?>">Forgot password?</a></div>
<button class="primary-action" type="submit">Sign in <i data-lucide="arrow-right"></i></button>
<p class="auth-switch">New to Udyam? <a href="<?=htmlspecialchars(url('/customer/register'))?>">Create an account</a></p></form>
<?php if ($unverifiedEmail): ?>
<details class="auth-resend" open><summary>Didn't get the verification email? Resend it</summary>
<form method="post" action="<?=htmlspecialchars(url('/customer/resend-verification'))?>"><?=csrf_field()?>
<label>Email address</label><div class="field-icon"><i data-lucide="mail"></i><input type="email" name="email" value="<?=htmlspecialchars($unverifiedEmail)?>" autocomplete="email" required></div>
<button class="secondary-action" type="submit">Resend verification email</button></form></details>
<?php endif; ?>