<?php
$errors = \App\Core\Session::get('errors', []);
$old = \App\Core\Session::get('old', []);
\App\Core\Session::forget('errors');
\App\Core\Session::forget('old');
$values = array_merge($record, $old);
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
        <div class="form-group"><label for="status">Status</label>
            <select class="form-control" id="status" name="status">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value) ?>" <?= ($values['status'] ?? array_key_first($statusOptions)) === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label for="sort_order">Sort Order</label>
            <input class="form-control" type="number" min="0" id="sort_order" name="sort_order" value="<?= (int) ($values['sort_order'] ?? 0) ?>">
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update' : 'Create' ?> <?= htmlspecialchars($definition['singular']) ?></button>
    </div></div>
</form>
