<?php
$errors = \App\Core\Session::get('import_errors', []);
\App\Core\Session::forget('import_errors');
?>
<section class="module-import-page"><div class="page-header"><div><span class="module-import-kicker"><i data-lucide="layers-3"></i> Funding intelligence</span><h2>Bulk Import Tenders &amp; Notices</h2><p>Upload opportunities using the same columns as the Udyam Funding Project Bulletin.</p></div><a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/tenders')) ?>"><i data-lucide="arrow-left"></i> Back to records</a></div>
<?php if ($errors): ?><div class="alert alert-danger"><strong>Import could not be completed.</strong><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card module-import-card"><div class="card-body">
    <div class="module-import-intro"><div><span class="module-import-step">Step 1</span><h4>Start with the Udyam template</h4><p>It already includes every required bulletin column in the correct order.</p></div><a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/tenders/import-template')) ?>"><i data-lucide="download"></i> Download CSV template</a></div>
    <div class="module-import-columns"><span><b>Required</b> State / Region · Invited By · Tender / Project Details · Last Date · Time · Submission Mode</span><span><b>Optional</b> Record Type · Document URL · Additional Notes</span></div>
    <form method="post" action="<?= htmlspecialchars(url('/admin/tenders/import')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="module-file-drop"><i data-lucide="file-spreadsheet"></i><div><label for="import_file">Choose your completed CSV file</label><small>Maximum 5 MB. Rows are published immediately; “Open Throughout Year” does not need a date.</small></div><input class="form-control" id="import_file" name="import_file" type="file" accept=".csv,text/csv" required></div>
        <button class="btn btn-primary module-import-submit" type="submit"><i data-lucide="upload-cloud"></i> Import records</button>
    </form>
</div></div></section>
