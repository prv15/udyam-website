<section class="media-library-hero">
    <div class="media-library-hero-copy"><span><i data-lucide="library"></i> Digital asset library</span><h2>Media Manager</h2><p>Store, find and reuse images, documents and campaign assets from one organised workspace.</p></div>
    <div class="media-library-stat"><strong><?= (int) $total ?></strong><span>asset<?= $total === 1 ? '' : 's' ?> in library</span></div>
</section>
<section class="media-library-toolbar">
    <form action="<?= htmlspecialchars(url('/admin/media')) ?>" method="get" class="media-library-search">
        <i data-lucide="search"></i><input type="search" name="search" placeholder="Search filename, title or asset…" value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off" data-media-live-search><button type="submit">Search</button>
        <?php if (($search ?? '') !== ''): ?><a href="<?= htmlspecialchars(url('/admin/media')) ?>" aria-label="Clear search"><i data-lucide="x"></i></a><?php endif; ?>
    </form>
    <a class="media-upload-trigger" href="#uploadMediaForm"><i data-lucide="upload-cloud"></i> Upload assets</a>
</section>
