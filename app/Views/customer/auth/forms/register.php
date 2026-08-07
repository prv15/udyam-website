<?php $pendingEmail = \App\Core\Session::getFlash('pending_verification_email'); ?>
<?php if ($pendingEmail): ?>
<div class="auth-heading"><span>Partner registration</span><h2>Check your email</h2><p>We've sent a verification link to <strong><?=htmlspecialchars($pendingEmail)?></strong>. Click it to activate your account, then sign in.</p></div>
<details class="auth-resend" open><summary>Didn't get the email? Resend it</summary>
<form method="post" action="<?=htmlspecialchars(url('/customer/resend-verification'))?>"><?=csrf_field()?>
<label>Email address</label><div class="field-icon"><i data-lucide="mail"></i><input type="email" name="email" value="<?=htmlspecialchars($pendingEmail)?>" autocomplete="email" required></div>
<button class="secondary-action" type="submit">Resend verification email</button></form></details>
<p class="auth-switch"><a href="<?=htmlspecialchars(url('/customer/login'))?>">Back to sign in</a></p>
<?php else: ?>
<div class="auth-heading"><span>Partner registration</span><h2>Create your workspace</h2><p>Start managing services and billing in one secure account.</p></div>
<form method="post" action="<?=htmlspecialchars(url('/customer/register'))?>"><?=csrf_field()?>
<div class="auth-grid"><div><label>Full name</label><input name="full_name" value="<?=htmlspecialchars($old['full_name']??'')?>" required></div><div><label>Company name</label><input name="company_name" value="<?=htmlspecialchars($old['company_name']??'')?>"></div>
<div><label>Mobile</label><input name="mobile" value="<?=htmlspecialchars($old['mobile']??'')?>" required></div><div><label>Email</label><input type="email" name="email" value="<?=htmlspecialchars($old['email']??'')?>" required></div>
<div><label>Password</label><input type="password" name="password" required></div><div><label>Confirm password</label><input type="password" name="password_confirmation" required></div></div>
<button class="primary-action" type="submit">Create secure account <i data-lucide="arrow-right"></i></button>
<p class="auth-switch">Already registered? <a href="<?=htmlspecialchars(url('/customer/login'))?>">Sign in</a></p></form>
<?php endif; ?>