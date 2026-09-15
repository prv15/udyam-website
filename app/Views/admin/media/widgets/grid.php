<?php
declare(strict_types=1);
use App\Helpers\FileHelper;
?>
<div class="media-asset-grid">
<?php foreach ($media as $item): $isImage=FileHelper::isImage($item['mime_type']);$extension=FileHelper::extension($item['filename']);$name=(string)($item['title']?:$item['filename']); ?>
    <article class="media-asset-card" data-media-asset data-media-searchable="<?= htmlspecialchars(strtolower($name.' '.$item['filename'].' '.$extension),ENT_QUOTES) ?>">
        <a class="media-asset-preview" href="<?= htmlspecialchars(media_url($item)) ?>" target="_blank" rel="noopener" title="Open <?= htmlspecialchars($name) ?>">
            <?php if($isImage): ?><img src="<?= htmlspecialchars(media_url($item)) ?>" alt="<?= htmlspecialchars($item['alt_text']?:$item['filename']) ?>" loading="lazy"><?php else: ?><span class="media-asset-file"><i data-lucide="file-text"></i><b><?= htmlspecialchars(strtoupper($extension ?: 'FILE')) ?></b></span><?php endif; ?>
            <span class="media-asset-open"><i data-lucide="external-link"></i></span>
        </a>
        <div class="media-asset-content"><span class="media-asset-kind"><?= htmlspecialchars(strtoupper($extension ?: 'FILE')) ?></span><h3 title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></h3><p><?= FileHelper::formatBytes((int)$item['file_size']) ?><?php if($isImage&&!empty($item['width'])): ?> · <?= (int)$item['width'] ?> × <?= (int)$item['height'] ?><?php endif; ?></p><small><i data-lucide="calendar"></i> <?= htmlspecialchars(date('d M Y',strtotime($item['created_at']))) ?></small></div>
        <div class="media-asset-actions"><a href="<?= htmlspecialchars(media_url($item)) ?>" target="_blank" rel="noopener"><i data-lucide="eye"></i> Preview</a><form action="<?= htmlspecialchars(url('/admin/media/delete/'.$item['id'])) ?>" method="post" class="delete-media-form"><?= csrf_field() ?><button type="submit" aria-label="Delete <?= htmlspecialchars($name) ?>"><i data-lucide="trash-2"></i></button></form></div>
    </article>
<?php endforeach; ?>
</div>
<p class="media-live-empty" data-media-live-empty hidden><i data-lucide="search-x"></i> No uploaded assets match this search on this page.</p>
