<?php $definitions = $template['sections']; ?>
<div class="page-header">
    <div><h2><?= htmlspecialchars($page['title']) ?> — Sections</h2><p>Enable, edit and arrange each website section independently.</p></div>
    <div>
        <a class="btn btn-secondary" target="_blank" href="<?= htmlspecialchars(url('/admin/pages/preview/' . $page['id'])) ?>">Preview Page</a>
        <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/pages/edit/' . $page['id'])) ?>">Page Settings</a>
    </div>
</div>

<form method="post" action="<?= htmlspecialchars(url('/admin/pages/sections/' . $page['id'] . '/reorder')) ?>">
    <?= csrf_field() ?>
    <div class="card"><div class="card-body">
        <div id="sectionList">
            <?php foreach ($sections as $section):
                $definition = $definitions[$section['section_key']] ?? null;
                if ($definition === null) continue;
            ?>
                <div class="section-manager-item" data-key="<?= htmlspecialchars($section['section_key']) ?>" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #e5e7eb">
                    <input type="hidden" name="section_order[]" value="<?= htmlspecialchars($section['section_key']) ?>">
                    <div style="flex:1">
                        <h3><?= htmlspecialchars($definition['label']) ?></h3>
                        <p><?= htmlspecialchars($definition['description']) ?></p>
                    </div>
                    <span class="badge <?= $section['is_enabled'] ? 'badge-success' : 'badge-warning' ?>"><?= $section['is_enabled'] ? 'Visible' : 'Hidden' ?></span>
                    <button class="btn btn-sm move-up" type="button">↑</button>
                    <button class="btn btn-sm move-down" type="button">↓</button>
                    <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(url('/admin/pages/sections/' . $page['id'] . '/edit/' . $section['section_key'])) ?>">Edit Section</a>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primary" type="submit" style="margin-top:16px">Save Section Order</button>
    </div></div>
</form>
<script>
document.getElementById('sectionList').addEventListener('click', event => {
    const item = event.target.closest('.section-manager-item');
    if (!item) return;
    if (event.target.classList.contains('move-up') && item.previousElementSibling) item.parentNode.insertBefore(item, item.previousElementSibling);
    if (event.target.classList.contains('move-down') && item.nextElementSibling) item.parentNode.insertBefore(item.nextElementSibling, item);
});
</script>
