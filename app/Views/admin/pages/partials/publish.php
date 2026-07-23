<?php

$page = $page ?? [];
$old = $old ?? ($_SESSION['old'] ?? []);

$status = $old['status'] ?? $page['status'] ?? 'draft';

$image = $old['featured_image'] ?? $page['featured_image'] ?? '';

$isEdit = isset($page['id']);

?>

<!-- Publish -->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0 fw-semibold">
            Publish
        </h5>

    </div>

    <div class="card-body">

        <div class="mb-3">

            <label class="form-label fw-semibold">

                Status

            </label>

            <select
                name="status"
                class="form-select"
            >

                <option value="draft"
                    <?= $status == 'draft' ? 'selected' : ''; ?>>
                    Draft
                </option>

                <option value="published"
                    <?= $status == 'published' ? 'selected' : ''; ?>>
                    Published
                </option>

                <option value="archived"
                    <?= $status == 'archived' ? 'selected' : ''; ?>>
                    Archived
                </option>

            </select>

        </div>

        <div class="d-grid gap-2">

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="bi bi-check-circle me-2"></i>

                <?= $isEdit ? 'Update Page' : 'Publish Page'; ?>

            </button>

            <a
                href="<?= htmlspecialchars(url('/admin/pages'), ENT_QUOTES, 'UTF-8') ?>"
                class="btn btn-outline-secondary"
            >

                Cancel

            </a>

        </div>

    </div>

</div>

<!-- Featured Image -->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0 fw-semibold">

            Featured Image

        </h5>

    </div>

    <div class="card-body">

        <input
            type="file"
            id="featuredImage"
            class="form-control mb-3"
            accept="image/*"
        >

        <input
            type="hidden"
            name="featured_image"
            id="featuredImagePath"
            value="<?= htmlspecialchars($image); ?>"
        >

        <div
            id="imagePreviewContainer"
            class="<?= empty($image) ? 'd-none' : ''; ?>"
        >

            <img
                id="imagePreview"
                src="<?= htmlspecialchars($image); ?>"
                class="img-fluid rounded border"
                alt="Featured Image"
            >

            <button
                type="button"
                class="btn btn-sm btn-outline-danger w-100 mt-3"
                id="removeImage"
            >

                Remove Image

            </button>

        </div>

    </div>

</div>

<?php if ($isEdit): ?>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0 fw-semibold">

            Information

        </h5>

    </div>

    <div class="card-body small">

        <div class="mb-2">

            <strong>ID</strong>

            <div>
                <?= $page['id']; ?>
            </div>

        </div>

        <div class="mb-2">

            <strong>Created</strong>

            <div>
                <?= $page['created_at'] ?? '-'; ?>
            </div>

        </div>

        <div class="mb-2">

            <strong>Updated</strong>

            <div>
                <?= $page['updated_at'] ?? '-'; ?>
            </div>

        </div>

        <div>

            <strong>Slug</strong>

            <div class="text-break">

                /<?= htmlspecialchars($page['slug']); ?>

            </div>

        </div>

    </div>

</div>

<?php endif; ?>

<script>

const imageInput = document.getElementById('featuredImage');

const previewContainer = document.getElementById('imagePreviewContainer');

const previewImage = document.getElementById('imagePreview');

const hiddenInput = document.getElementById('featuredImagePath');

const removeBtn = document.getElementById('removeImage');

if (imageInput) {

    imageInput.addEventListener('change', function(e){

        const file = e.target.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function(event){

            previewImage.src = event.target.result;

            previewContainer.classList.remove('d-none');

        };

        reader.readAsDataURL(file);

    });

}

if (removeBtn) {

    removeBtn.addEventListener('click', function(){

        imageInput.value = '';

        hiddenInput.value = '';

        previewImage.src = '';

        previewContainer.classList.add('d-none');

    });

}

</script>
