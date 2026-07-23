<?php
$isEdit = $mode === 'edit';
$values = array_merge($page, $old);
$action = $isEdit ? '/admin/pages/update/' . $page['id'] : '/admin/pages/store';
$value = static fn (string $field): string => htmlspecialchars((string) ($values[$field] ?? ''), ENT_QUOTES, 'UTF-8');
$error = static fn (string $field): string => htmlspecialchars((string) ($errors[$field][0] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="page-header">
    <div>
        <h2><?= $isEdit ? 'Edit Page' : 'Create New Page' ?></h2>
        <p>Create website content, control publishing, and configure search metadata.</p>
    </div>
    <div>
        <?php if ($isEdit): ?>
            <a class="btn btn-secondary" target="_blank" href="<?= htmlspecialchars(url('/admin/pages/preview/' . $page['id'])) ?>">Preview</a>
            <?php if (($values['template'] ?? 'default') !== 'default'): ?>
                <a class="btn btn-primary" href="<?= htmlspecialchars(url('/admin/pages/sections/' . $page['id'])) ?>">Edit Sections</a>
            <?php endif; ?>
        <?php endif; ?>
        <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/pages')) ?>">Back to Pages</a>
    </div>
</div>

<form action="<?= htmlspecialchars(url($action)) ?>" method="post" id="pageForm">
    <?= csrf_field() ?>
    <div class="content-grid">
        <div>
            <section class="card"><div class="card-body">
                <h3>Page Information</h3>
                <div class="form-group">
                    <label for="title">Page Title *</label>
                    <input class="form-control" id="title" name="title" maxlength="255" value="<?= $value('title') ?>" required>
                    <?php if ($error('title')): ?><div class="invalid-feedback d-block"><?= $error('title') ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="slug">URL Slug *</label>
                    <input class="form-control" id="slug" name="slug" maxlength="255" value="<?= $value('slug') ?>" placeholder="about-us" required>
                    <small class="text-muted">Public URL: /<span id="slugPreview"><?= $value('slug') ?></span></small>
                    <?php if ($error('slug')): ?><div class="invalid-feedback d-block"><?= $error('slug') ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="excerpt">Short Description</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500"><?= $value('excerpt') ?></textarea>
                    <?php if ($error('excerpt')): ?><div class="invalid-feedback d-block"><?= $error('excerpt') ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="content">Page Content</label>
                    <textarea class="form-control" id="content" name="content" rows="18"><?= $value('content') ?></textarea>
                    <small class="text-muted">Trusted HTML is supported for formatted website content.</small>
                </div>
            </div></section>

            <section class="card"><div class="card-body">
                <h3>Search Engine Optimization</h3>
                <div class="form-group"><label for="seo_title">SEO Title</label><input class="form-control" id="seo_title" name="seo_title" maxlength="255" value="<?= $value('seo_title') ?>"></div>
                <div class="form-group"><label for="seo_description">SEO Description</label><textarea class="form-control" id="seo_description" name="seo_description" rows="3" maxlength="320"><?= $value('seo_description') ?></textarea>
                    <?php if ($error('seo_description')): ?><div class="invalid-feedback d-block"><?= $error('seo_description') ?></div><?php endif; ?>
                </div>
                <div class="form-group"><label for="seo_keywords">SEO Keywords</label><input class="form-control" id="seo_keywords" name="seo_keywords" maxlength="500" value="<?= $value('seo_keywords') ?>"></div>
            </div></section>
        </div>

        <aside>
            <section class="card"><div class="card-body">
                <h3>Publishing</h3>
                <div class="form-group">
                    <label for="template">Page Template *</label>
                    <select class="form-control" id="template" name="template">
                        <?php foreach ((require CONFIG_PATH . '/page-templates.php') as $templateKey => $template): ?>
                            <option value="<?= htmlspecialchars($templateKey) ?>" <?= ($values['template'] ?? 'default') === $templateKey ? 'selected' : '' ?>><?= htmlspecialchars($template['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($error('template')): ?><div class="invalid-feedback d-block"><?= $error('template') ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select class="form-control" id="status" name="status">
                        <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $statusValue => $label): ?>
                            <option value="<?= $statusValue ?>" <?= ($values['status'] ?? 'draft') === $statusValue ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($error('status')): ?><div class="invalid-feedback d-block"><?= $error('status') ?></div><?php endif; ?>
                </div>
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Changes' : 'Create Page' ?></button>
            </div></section>

            <section class="card"><div class="card-body">
                <h3>Featured Image</h3>
                <div class="form-group">
                    <label for="featured_image">Media Library Image</label>
                    <select class="form-control" id="featured_image" name="featured_image">
                        <option value="">No featured image</option>
                        <?php foreach ($mediaImages as $image): ?>
                            <option value="<?= (int) $image['id'] ?>" data-preview="<?= htmlspecialchars(media_url($image)) ?>" <?= (int) ($values['featured_image'] ?? 0) === (int) $image['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($image['title'] ?: $image['original_name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($error('featured_image')): ?><div class="invalid-feedback d-block"><?= $error('featured_image') ?></div><?php endif; ?>
                </div>
                <?php
                $selectedImageUrl = '';
                foreach ($mediaImages as $image) {
                    if ((int) ($values['featured_image'] ?? 0) === (int) $image['id']) {
                        $selectedImageUrl = media_url($image);
                        break;
                    }
                }
                ?>
                <img id="imagePreview" src="<?= htmlspecialchars($selectedImageUrl) ?>" alt="" style="max-width:100%;<?= $selectedImageUrl === '' ? 'display:none;' : '' ?>">
            </div></section>

            <?php if ($isEdit): ?>
                <section class="card"><div class="card-body">
                    <h3>Page Details</h3>
                    <p><strong>ID:</strong> <?= (int) $page['id'] ?></p>
                    <p><strong>Created:</strong> <?= htmlspecialchars((string) $page['created_at']) ?></p>
                    <p><strong>Updated:</strong> <?= htmlspecialchars((string) $page['updated_at']) ?></p>
                </div></section>
            <?php endif; ?>
        </aside>
    </div>
</form>

<script>
(() => {
    const title = document.getElementById('title');
    const slug = document.getElementById('slug');
    const slugPreview = document.getElementById('slugPreview');
    const image = document.getElementById('featured_image');
    const preview = document.getElementById('imagePreview');
    let slugEdited = slug.value.trim() !== '';
    const slugify = value => value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    slug.addEventListener('input', () => { slugEdited = true; slug.value = slugify(slug.value); slugPreview.textContent = slug.value; });
    title.addEventListener('input', () => { if (!slugEdited) { slug.value = slugify(title.value); slugPreview.textContent = slug.value; } });
    image.addEventListener('change', () => {
        const option = image.options[image.selectedIndex];
        preview.src = option.dataset.preview || '';
        preview.style.display = preview.src ? 'block' : 'none';
    });
})();
</script>
