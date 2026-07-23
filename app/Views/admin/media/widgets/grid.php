<?php

declare(strict_types=1);

use App\Helpers\FileHelper;

?>

<div class="row g-4">

    <?php foreach ($media as $item): ?>

        <?php

        $isImage = FileHelper::isImage($item['mime_type']);

        $extension = FileHelper::extension($item['filename']);

        ?>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">

            <div class="card media-card h-100 border-0 shadow-sm">

                <div class="media-thumbnail">

                    <?php if ($isImage): ?>

                        <img
                            src="<?= htmlspecialchars(media_url($item)) ?>"
                            alt="<?= htmlspecialchars($item['alt_text'] ?: $item['filename']) ?>"
                            loading="lazy">

                    <?php else: ?>

                        <div class="media-file-icon">

                            <i class="bi <?= FileHelper::icon($extension) ?>"></i>

                        </div>

                    <?php endif; ?>

                </div>

                <div class="card-body">

                    <h6
                        class="media-title"
                        title="<?= htmlspecialchars($item['filename']) ?>">

                        <?= htmlspecialchars($item['title'] ?: $item['filename']) ?>

                    </h6>

                    <div class="small text-muted">

                        <?= strtoupper($extension) ?>

                    </div>

                    <div class="small text-muted">

                        <?= FileHelper::formatBytes((int) $item['file_size']) ?>

                    </div>

                    <?php if ($isImage && !empty($item['width'])): ?>

                        <div class="small text-muted">

                            <?= $item['width'] ?> × <?= $item['height'] ?>

                        </div>

                    <?php endif; ?>

                    <div class="small text-muted">

                        <?= date('d M Y', strtotime($item['created_at'])) ?>

                    </div>

                </div>

                <div class="card-footer bg-white border-0">

                    <div class="d-flex justify-content-between">

                        <form
                            action="<?= htmlspecialchars(url('/admin/media/delete/' . $item['id'])) ?>"
                            method="POST"
                            class="delete-media-form">

                            <?= csrf_field() ?>

                            <button
                                type="submit"
                                class="btn btn-outline-danger btn-sm">

                                <i class="bi bi-trash"></i>

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>
