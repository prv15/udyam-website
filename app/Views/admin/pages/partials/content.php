<?php

$page = $page ?? [];
$old = $_SESSION['old'] ?? [];

$content = htmlspecialchars(
    $old['content'] ?? $page['content'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

?>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

        <h5 class="mb-0 fw-semibold">
            Page Content
        </h5>

        <small class="text-muted">
            Main content displayed on the page
        </small>

    </div>

    <div class="card-body">

        <textarea
            id="content"
            name="content"
            class="form-control"
            rows="20"
        ><?= $content; ?></textarea>

    </div>

</div>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white py-3">

        <h6 class="mb-0 fw-semibold">

            Content Statistics

        </h6>

    </div>

    <div class="card-body">

        <div class="row text-center">

            <div class="col-md-3">

                <h3
                    id="wordCount"
                    class="fw-bold text-primary mb-1"
                >
                    0
                </h3>

                <small class="text-muted">
                    Words
                </small>

            </div>

            <div class="col-md-3">

                <h3
                    id="charCount"
                    class="fw-bold text-success mb-1"
                >
                    0
                </h3>

                <small class="text-muted">
                    Characters
                </small>

            </div>

            <div class="col-md-3">

                <h3
                    id="readingTime"
                    class="fw-bold text-warning mb-1"
                >
                    0 min
                </h3>

                <small class="text-muted">
                    Reading Time
                </small>

            </div>

            <div class="col-md-3">

                <h3
                    id="paragraphCount"
                    class="fw-bold text-info mb-1"
                >
                    0
                </h3>

                <small class="text-muted">
                    Paragraphs
                </small>

            </div>

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    function updateStatistics() {

        let text = '';

        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {

            text = tinymce.get('content').getContent({
                format: 'text'
            });

        } else {

            text = document.getElementById('content').value;

        }

        const characters = text.length;

        const words = text
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        const wordCount = words.length;

        const reading = Math.max(
            1,
            Math.ceil(wordCount / 200)
        );

        const paragraphs = text
            .split(/\n+/)
            .filter(Boolean).length;

        document.getElementById('charCount').innerText = characters;

        document.getElementById('wordCount').innerText = wordCount;

        document.getElementById('readingTime').innerText = reading + ' min';

        document.getElementById('paragraphCount').innerText = paragraphs;

    }

    document
        .getElementById('content')
        .addEventListener('input', updateStatistics);

    updateStatistics();

});
</script>