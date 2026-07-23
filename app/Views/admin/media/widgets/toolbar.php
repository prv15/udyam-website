<?php

declare(strict_types=1);
?>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-body">

        <div class="row align-items-center g-3">

            <!-- Left -->

            <div class="col-lg-4">

                <h3 class="mb-1 fw-bold">
                    Media Library
                </h3>

                <p class="text-muted mb-0">
                    Upload, search and manage your media files.
                </p>

            </div>

            <!-- Center -->

            <div class="col-lg-5">

                <form action="<?= htmlspecialchars(url('/admin/media')) ?>" method="GET">

                    <div class="input-group">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="text"
                            class="form-control"
                            name="search"
                            placeholder="Search by filename, title..."
                            value="<?= htmlspecialchars($search ?? '') ?>"
                        >

                        <button
                            class="btn btn-outline-secondary"
                            type="submit">

                            Search

                        </button>

                    </div>

                </form>

            </div>

            <!-- Right -->

            <div class="col-lg-3 text-lg-end">

                <a class="btn btn-primary" href="#uploadMediaForm">

                    <i class="bi bi-cloud-upload me-2"></i>

                    Upload Media

                </a>

            </div>

        </div>

    </div>

</div>
