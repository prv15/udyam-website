'use strict';

document.addEventListener('DOMContentLoaded', () => {

    initDeleteConfirmation();
    initUploadPreview();
    initSearchFocus();

});

/**
 * Delete confirmation
 */
function initDeleteConfirmation()
{
    document.querySelectorAll('.delete-media-form').forEach(form => {

        form.addEventListener('submit', function (e) {

            if (!confirm('Are you sure you want to delete this media file?')) {
                e.preventDefault();
            }

        });

    });
}

/**
 * Preview selected image
 */
function initUploadPreview()
{
    const input = document.querySelector('input[name="media"]');

    if (!input) {
        return;
    }

    const preview = document.getElementById('mediaPreview');

    input.addEventListener('change', function () {

        if (!preview) {
            return;
        }

        preview.innerHTML = '';

        if (!this.files.length) {
            return;
        }

        const file = this.files[0];

        if (!file.type.startsWith('image/')) {

            preview.innerHTML = `
                <div class="alert alert-info mb-0">
                    <strong>${file.name}</strong><br>
                    ${Math.round(file.size / 1024)} KB
                </div>
            `;

            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {

            preview.innerHTML = `
                <img
                    src="${e.target.result}"
                    class="img-fluid rounded shadow-sm"
                    style="max-height:250px;">
            `;

        };

        reader.readAsDataURL(file);

    });

}

/**
 * Focus search field with Ctrl + K
 */
function initSearchFocus()
{
    const search = document.querySelector('input[name="search"]');

    if (!search) {
        return;
    }

    document.addEventListener('keydown', function (e) {

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {

            e.preventDefault();

            search.focus();

            search.select();

        }

    });

}