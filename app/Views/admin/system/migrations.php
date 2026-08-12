<div class="page-header"><div><span class="page-eyebrow">Secure maintenance</span><h2>System Updates</h2><p>Apply the additive Enterprise ERP database update without exposing migration files to the web.</p></div></div>

<section class="form-card" style="max-width:760px">
    <div class="card-heading"><div><span>Enterprise ERP extension</span><h3><?= $erpInstalled ? 'Update installed' : 'Database update required' ?></h3></div><i data-lucide="database-zap"></i></div>
    <?php if ($erpInstalled): ?>
        <div class="alert alert-success">Notifications, tender deadline automation, subscription requests and customer support tables are ready.</div>
    <?php else: ?>
        <p>This creates only the new additive ERP tables. It does not delete, replace or alter existing customer data.</p>
        <form method="post" action="<?= htmlspecialchars(url('/admin/system/migrations/enterprise-erp')) ?>" class="admin-form" style="margin-top:20px">
            <?= csrf_field() ?>
            <label>Type <strong>RUN ERP MIGRATION</strong> to confirm
                <input name="confirmation" autocomplete="off" required placeholder="RUN ERP MIGRATION">
            </label>
            <button class="btn btn-primary" type="submit"><i data-lucide="play"></i> Apply Enterprise ERP Update</button>
        </form>
    <?php endif; ?>
</section>

<section class="form-card" style="max-width:760px;margin-top:20px">
    <div class="card-heading"><div><span>Udyam Ventures</span><h3><?= $staffInstalled ? 'Staff module installed' : 'Staff database update required' ?></h3></div><i data-lucide="contact-round"></i></div>
    <?php if ($staffInstalled): ?>
        <div class="alert alert-success">Users-backed staff profiles, roles, Digital Business Cards, QR metadata and privacy-conscious analytics are ready.</div>
    <?php else: ?>
        <p>This extends the shared users table with staff fields and adds role permissions and business cards. Existing admins and partners are preserved.</p>
        <form method="post" action="<?= htmlspecialchars(url('/admin/system/migrations/staff-management')) ?>" class="admin-form" style="margin-top:20px">
            <?= csrf_field() ?>
            <label>Type <strong>RUN STAFF MIGRATION</strong> to confirm
                <input name="confirmation" autocomplete="off" required placeholder="RUN STAFF MIGRATION">
            </label>
            <button class="btn btn-primary" type="submit"><i data-lucide="play"></i> Install Staff Management</button>
        </form>
    <?php endif; ?>
</section>
