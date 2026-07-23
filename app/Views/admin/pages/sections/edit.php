<?php $values = $section['data']; ?>
<div class="page-header">
    <div><h2><?= htmlspecialchars($definition['label']) ?></h2><p><?= htmlspecialchars($definition['description']) ?></p></div>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/pages/sections/' . $page['id'])) ?>">Back to Sections</a>
</div>
<form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars(url('/admin/pages/sections/' . $page['id'] . '/update/' . $sectionKey)) ?>">
    <?= csrf_field() ?>
    <div class="card"><div class="card-body">
        <div class="form-group">
            <label><input type="checkbox" name="is_enabled" value="1" <?= $section['is_enabled'] ? 'checked' : '' ?>> Show this section on the website</label>
        </div>
        <?php foreach ($definition['fields'] as $name => $field): ?>
            <div class="form-group">
                <label><?= htmlspecialchars($field['label']) ?></label>
                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea class="form-control" name="<?= htmlspecialchars($name) ?>" rows="5"><?= htmlspecialchars((string) ($values[$name] ?? '')) ?></textarea>
                <?php elseif ($field['type'] === 'media'): ?>
                    <select class="form-control" name="<?= htmlspecialchars($name) ?>">
                        <option value="">No image selected</option>
                        <?php foreach ($mediaImages as $image): ?>
                            <option value="<?= (int) $image['id'] ?>" <?= (int) ($values[$name] ?? 0) === (int) $image['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) ($image['title'] ?: $image['original_name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="section-media-upload">
                        <input class="form-control" type="file" name="upload_<?= htmlspecialchars($name) ?>" accept="image/jpeg,image/png,image/webp,image/gif">
                        <button
                            class="btn btn-secondary"
                            type="submit"
                            name="upload_field"
                            value="<?= htmlspecialchars($name) ?>"
                            formaction="<?= htmlspecialchars(url('/admin/pages/sections/' . $page['id'] . '/upload/' . $sectionKey)) ?>"
                        >Upload &amp; Use This Image</button>
                    </div>
                    <small class="form-help">Upload JPG, PNG, WebP or GIF here, or choose an existing Media Library image above.</small>
                <?php elseif ($field['type'] === 'repeater'): ?>
                    <div class="repeater" data-name="<?= htmlspecialchars($name) ?>">
                        <div class="repeater-rows">
                        <?php foreach (($values[$name] ?? [[]]) as $row): ?>
                            <div class="repeater-row" style="display:grid;grid-template-columns:repeat(<?= count($field['fields']) ?>,1fr) auto;gap:10px;margin-bottom:10px">
                                <?php foreach ($field['fields'] as $subName => $label): ?>
                                    <input class="form-control" name="<?= htmlspecialchars($name) ?>[<?= htmlspecialchars($subName) ?>][]" value="<?= htmlspecialchars((string) ($row[$subName] ?? '')) ?>" placeholder="<?= htmlspecialchars($label) ?>">
                                <?php endforeach; ?>
                                <button class="btn btn-danger remove-row" type="button">Remove</button>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <template><div class="repeater-row" style="display:grid;grid-template-columns:repeat(<?= count($field['fields']) ?>,1fr) auto;gap:10px;margin-bottom:10px">
                            <?php foreach ($field['fields'] as $subName => $label): ?><input class="form-control" name="<?= htmlspecialchars($name) ?>[<?= htmlspecialchars($subName) ?>][]" placeholder="<?= htmlspecialchars($label) ?>"><?php endforeach; ?>
                            <button class="btn btn-danger remove-row" type="button">Remove</button>
                        </div></template>
                        <button class="btn btn-secondary add-row" type="button">Add Item</button>
                    </div>
                <?php else: ?>
                    <input class="form-control" type="<?= $field['type'] === 'number' ? 'number' : 'text' ?>" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars((string) ($values[$name] ?? '')) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button class="btn btn-primary" type="submit">Save Section</button>
    </div></div>
</form>
<script>
document.addEventListener('click', event => {
    if (event.target.classList.contains('add-row')) {
        const repeater = event.target.closest('.repeater');
        repeater.querySelector('.repeater-rows').append(repeater.querySelector('template').content.cloneNode(true));
    }
    if (event.target.classList.contains('remove-row')) event.target.closest('.repeater-row').remove();
});
</script>
<style>
.section-media-upload{display:grid;grid-template-columns:minmax(220px,1fr) auto;gap:10px;margin-top:10px;align-items:center}
.form-help{display:block;color:#667085;margin-top:7px}
@media(max-width:720px){.section-media-upload{grid-template-columns:1fr}}
</style>
