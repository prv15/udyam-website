<?php

declare(strict_types=1);
?>

<section class="card shadow-sm border-0 mt-4" id="uploadMediaForm">

            <form
                action="<?= htmlspecialchars(url('/admin/media/upload')) ?>"
                method="POST"
                enctype="multipart/form-data">

                <?= csrf_field() ?>

                <div class="card-header bg-white">

                    <h5
                        class="modal-title fw-bold"
                        >

                        Upload Media

                    </h5>

                </div>

                <div class="card-body">

                    <div class="mb-4">

                        <label class="form-label fw-semibold">

                            Select File

                        </label>

                        <input
                            class="form-control"
                            type="file"
                            name="media"
                            required>
                            <div id="mediaPreview" class="mt-3 text-center">
                            </div>

                        <div class="form-text">

                            Supported:
                            JPG,
                            PNG,
                            GIF,
                            WEBP,
                            SVG,
                            PDF,
                            DOC,
                            DOCX,
                            XLS,
                            XLSX,
                            ZIP,
                            MP4 and more.

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">

                                Title

                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">

                                Alt Text

                            </label>

                            <input
                                type="text"
                                name="alt_text"
                                class="form-control">

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Caption

                        </label>

                        <input
                            type="text"
                            name="caption"
                            class="form-control">

                    </div>

                    <div class="mb-0">

                        <label class="form-label">

                            Description

                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            class="form-control"></textarea>

                    </div>

                </div>

                <div class="card-footer bg-white text-end">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-cloud-upload me-2"></i>

                        Upload Media

                    </button>

                </div>

            </form>

</section>
