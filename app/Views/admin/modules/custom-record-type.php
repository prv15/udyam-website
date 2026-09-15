<?php
$typeOptions = $field['options'];
$selectedType = (string) ($values[$name] ?? array_key_first($typeOptions));
if ($selectedType !== '' && !array_key_exists($selectedType, $typeOptions)) {
    $typeOptions[$selectedType] = $selectedType;
}
?>
<div class="record-type-control" style="position:relative">
    <select class="form-control" id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" required style="padding-right:76px">
        <?php foreach ($typeOptions as $value => $label): ?>
            <option value="<?= htmlspecialchars((string) $value) ?>" <?= $selectedType === (string) $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="button" aria-label="Add a custom record type" title="Add a custom record type" aria-expanded="false" aria-controls="new-record-type" data-add-record-type style="position:absolute;right:30px;top:50%;transform:translateY(-50%);width:30px;height:30px;border:1px solid #d7d1ff;border-radius:6px;background:#f7f5ff;color:#5845cb;font-size:22px;line-height:1;cursor:pointer">+</button>
</div>
<div id="new-record-type" hidden style="margin-top:8px">
    <label for="custom_record_type">New record type</label>
    <div style="display:flex;gap:8px">
        <input class="form-control" id="custom_record_type" maxlength="60" placeholder="e.g. Expression of Interest">
        <button class="btn btn-secondary" type="button" data-save-record-type>Add</button>
        <button class="btn btn-secondary" type="button" data-cancel-record-type>Cancel</button>
    </div>
    <small>Add the type, then save the record to make it available for future tenders.</small>
</div>
<script>
(() => {
    const add = document.querySelector('[data-add-record-type]');
    const select = add.parentElement.querySelector('select');
    const editor = document.getElementById('new-record-type');
    const input = document.getElementById('custom_record_type');
    function close() {
        editor.hidden = true;
        input.value = '';
        input.setCustomValidity('');
        add.setAttribute('aria-expanded', 'false');
        select.focus();
    }
    function save() {
        const value = input.value.trim();
        input.setCustomValidity(value ? '' : 'Enter a record type.');
        if (!input.reportValidity()) return false;
        const existing = Array.from(select.options).find(option =>
            option.value.toLowerCase() === value.toLowerCase() || option.text.toLowerCase() === value.toLowerCase());
        if (existing) select.value = existing.value;
        else {
            select.add(new Option(value, value));
            select.value = value;
        }
        close();
        return true;
    }
    add.addEventListener('click', () => {
        editor.hidden = false;
        add.setAttribute('aria-expanded', 'true');
        input.focus();
    });
    input.addEventListener('input', () => input.setCustomValidity(''));
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); save(); }
        if (event.key === 'Escape') { event.preventDefault(); close(); }
    });
    editor.querySelector('[data-save-record-type]').addEventListener('click', save);
    editor.querySelector('[data-cancel-record-type]').addEventListener('click', close);
    select.form.addEventListener('submit', event => {
        if (!editor.hidden && !save()) event.preventDefault();
    });
})();
</script>
