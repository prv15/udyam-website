<?php

$page = $page ?? [];
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

function oldValue(string $field, array $page, array $old)
{
    return htmlspecialchars($old[$field] ?? $page[$field] ?? '', ENT_QUOTES, 'UTF-8');
}

function fieldError(array $errors, string $field)
{
    return isset($errors[$field]) ? 'is-invalid' : '';
}

?>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white py-3">

        <h5 class="mb-0 fw-semibold">
            Basic Information
        </h5>

    </div>

    <div class="card-body">

        <!-- Page Title -->

        <div class="mb-4">

            <label class="form-label fw-semibold">
                Page Title
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                class="form-control <?= fieldError($errors, 'title'); ?>"
                id="title"
                name="title"
                maxlength="255"
                placeholder="Enter page title..."
                value="<?= oldValue('title', $page, $old); ?>"
                required
            >

            <?php if(isset($errors['title'])): ?>

                <div class="invalid-feedback">

                    <?= $errors['title'][0]; ?>

                </div>

            <?php endif; ?>

            <div class="text-end">

                <small class="text-muted">

                    <span id="titleCounter">
                        <?= strlen($old['title'] ?? $page['title'] ?? ''); ?>
                    </span>/255

                </small>

            </div>

        </div>

        <!-- Slug -->

        <div class="mb-4">

            <label class="form-label fw-semibold">
                Slug
                <span class="text-danger">*</span>
            </label>

            <div class="input-group">

                <span class="input-group-text">
                    /
                </span>

                <input
                    type="text"
                    class="form-control <?= fieldError($errors, 'slug'); ?>"
                    id="slug"
                    name="slug"
                    maxlength="255"
                    placeholder="page-slug"
                    value="<?= oldValue('slug', $page, $old); ?>"
                    required
                >

            </div>

            <?php if(isset($errors['slug'])): ?>

                <div class="invalid-feedback d-block">

                    <?= $errors['slug'][0]; ?>

                </div>

            <?php endif; ?>

            <small class="text-muted">

                Example:
                <strong>/about-us</strong>

            </small>

        </div>

        <!-- Excerpt -->

        <div>

            <label class="form-label fw-semibold">

                Short Description

            </label>

            <textarea
                class="form-control"
                rows="4"
                id="excerpt"
                name="excerpt"
                maxlength="500"
                placeholder="Write a short description of this page..."
            ><?= oldValue('excerpt', $page, $old); ?></textarea>

            <div class="text-end">

                <small class="text-muted">

                    <span id="excerptCounter">

                        <?= strlen($old['excerpt'] ?? $page['excerpt'] ?? ''); ?>

                    </span>/500

                </small>

            </div>

        </div>

    </div>

</div>

<?php

unset($_SESSION['errors']);
unset($_SESSION['old']);

?>