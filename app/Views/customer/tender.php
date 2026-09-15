<?php
$deadline = (string) ($tender['deadline_label'] ?? $tender['closing_date'] ?? 'Open throughout year');
$document = trim((string) ($tender['document_url'] ?? ''));
$fullTitle = $fullTitle ?? (string) $tender['title'];
$type = (string) ($tender['type'] ?? 'tender');
$submission = trim((string) ($tender['submission_mode'] ?? ''));
if (($tender['submission_time'] ?? '') !== '') $submission .= ' · ' . (string) $tender['submission_time'];
?>
<a class="tender-back" href="<?= htmlspecialchars(url('/customer/tenders')) ?>"><i data-lucide="arrow-left"></i> All notices &amp; tenders</a>
<section class="portal-card tender-detail-page">
    <span class="tender-directory-type <?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucfirst($type)) ?></span>
    <h2><?= htmlspecialchars($fullTitle) ?></h2>
    <dl>
        <div><dt>Invited by</dt><dd><?= htmlspecialchars((string) ($tender['invited_by'] ?? $tender['department'] ?? '—')) ?></dd></div>
        <div><dt>State / Region</dt><dd><?= htmlspecialchars((string) ($tender['region'] ?? 'PAN INDIA')) ?></dd></div>
        <div><dt>Last date</dt><dd><?= htmlspecialchars($deadline) ?></dd></div>
        <div><dt>Submission</dt><dd><?= htmlspecialchars($submission) ?></dd></div>
    </dl>
    <?php if (!empty($tender['description'])): ?>
        <div class="tender-detail-notes"><h3>Additional details</h3><p><?= nl2br(htmlspecialchars((string) $tender['description'])) ?></p></div>
    <?php endif; ?>
    <?php if ($document !== '' && $document !== '#'): ?>
        <a class="primary-action" href="<?= htmlspecialchars($document) ?>" target="_blank" rel="noopener">Open tender document <i data-lucide="external-link"></i></a>
    <?php else: ?>
        <p class="tender-document-note"><i data-lucide="info"></i> The official tender document link will be added shortly.</p>
    <?php endif; ?>
</section>
