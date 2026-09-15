<?php
$errors = \App\Core\Session::get('errors', []);
$old = \App\Core\Session::get('old', []);
\App\Core\Session::forget('errors');
\App\Core\Session::forget('old');
$values = array_merge($record, $old);
if ($module === 'tenders') {
    $values['invited_by'] = $values['invited_by'] ?? ($values['department'] ?? '');
    $values['deadline_label'] = $values['deadline_label'] ?? ($values['closing_date'] ?? '');
}
$isEdit = $mode === 'edit';
$action = $isEdit ? '/admin/' . $module . '/update/' . $record['id'] : '/admin/' . $module . '/store';
?>
<div class="page-header">
    <div><h2><?= htmlspecialchars($title) ?></h2><p>Fields marked with * are required.</p></div>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/' . $module)) ?>">Back</a>
</div>
<form method="post" action="<?= htmlspecialchars(url($action)) ?>">
    <?= csrf_field() ?>
    <div class="card"><div class="card-body">
        <?php foreach ($definition['fields'] as $name => $field): ?>
            <div class="form-group">
                <label for="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($field['label']) ?><?= ($field['required'] ?? false) ? ' *' : '' ?></label>
                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea class="form-control" id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" rows="6"><?= htmlspecialchars((string) ($values[$name] ?? '')) ?></textarea>
                <?php elseif ($field['type'] === 'select' && ($field['allow_custom'] ?? false)): ?>
                    <?php require __DIR__ . '/custom-record-type.php'; ?>
                <?php elseif ($field['type'] === 'select'): ?>
                    <select class="form-control" id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>">
                        <?php foreach ($field['options'] as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ($values[$name] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input class="form-control" type="<?= htmlspecialchars($field['type']) ?>" id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars((string) ($values[$name] ?? '')) ?>" <?= isset($field['maxlength']) ? 'maxlength="' . (int) $field['maxlength'] . '"' : '' ?>>
                <?php endif; ?>
                <?php if (isset($errors[$name][0])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($errors[$name][0]) ?></div><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="form-group<?= $module === 'tenders' ? ' tender-status-editor' : '' ?>"><label for="status">Status</label>
            <?php $selectedStatus=(string)($values['status'] ?? array_key_first($statusOptions));$isCustomTenderStatus=$module==='tenders'&&!array_key_exists($selectedStatus,$statusOptions); ?>
            <div class="tender-status-control">
                <select class="form-control" id="status" name="status" data-tender-status-select>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $selectedStatus === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                    <?php if($module==='tenders'): ?><option value="custom" <?= $isCustomTenderStatus?'selected':'' ?>>Custom status…</option><?php endif; ?>
                </select>
                <?php if($module==='tenders'): ?><button class="tender-status-add" type="button" data-tender-status-add title="Add a custom status"><i data-lucide="plus"></i><span>Add status</span></button><?php endif; ?>
            </div>
            <?php if($module==='tenders'): ?><div class="tender-custom-status" data-tender-custom-status <?= $isCustomTenderStatus?'':'hidden' ?>><label for="custom_status">New status name</label><input class="form-control" id="custom_status" name="custom_status" value="<?=htmlspecialchars($isCustomTenderStatus?ucwords(str_replace('-',' ',$selectedStatus)):'')?>" maxlength="60" placeholder="e.g. Under review"></div><?php endif; ?>
        </div>
        <div class="form-group"><label for="sort_order">Sort Order</label>
            <input class="form-control" type="number" min="0" id="sort_order" name="sort_order" value="<?= (int) ($values['sort_order'] ?? 0) ?>">
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update' : 'Create' ?> <?= htmlspecialchars($definition['singular']) ?></button>
    </div></div>
</form>
<?php if($module==='tenders'): ?><script>document.addEventListener('DOMContentLoaded',function(){var select=document.querySelector('[data-tender-status-select]'),custom=document.querySelector('[data-tender-custom-status]'),add=document.querySelector('[data-tender-status-add]');if(!select||!custom)return;function sync(){custom.hidden=select.value!=='custom';}select.addEventListener('change',sync);add&&add.addEventListener('click',function(){select.value='custom';sync();custom.querySelector('input').focus();});sync();});</script><?php endif; ?>
