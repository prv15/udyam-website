<section class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">SEO</h5></div>
    <div class="card-body">
        <div class="mb-3">
            <label for="seo_title" class="form-label">SEO title</label>
            <input id="seo_title" name="seo_title" class="form-control" maxlength="255"
                   value="<?= htmlspecialchars($page['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
            <label for="seo_description" class="form-label">SEO description</label>
            <textarea id="seo_description" name="seo_description" class="form-control" rows="3" maxlength="320"><?= htmlspecialchars($page['seo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="mb-0">
            <label for="seo_keywords" class="form-label">SEO keywords</label>
            <input id="seo_keywords" name="seo_keywords" class="form-control" maxlength="500"
                   value="<?= htmlspecialchars($page['seo_keywords'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>
</section>
