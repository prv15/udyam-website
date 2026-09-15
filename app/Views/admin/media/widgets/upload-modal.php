<section class="media-upload-panel" id="uploadMediaForm">
    <div class="media-upload-panel-heading"><div><span>Asset intake</span><h3>Upload to media library</h3><p>Add a meaningful title and alt text so your team can find and reuse assets quickly.</p></div><i data-lucide="cloud-upload"></i></div>
    <form action="<?= htmlspecialchars(url('/admin/media/upload')) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label class="media-file-picker"><input type="file" name="media" required><i data-lucide="upload"></i><strong>Choose a file or drag it here</strong><span>Images, documents, video and archive files are supported.</span></label>
        <div id="mediaPreview" class="media-upload-preview"></div>
        <div class="media-upload-fields"><label><span>Title <em>Optional</em></span><input type="text" name="title" placeholder="e.g. CSR programme brochure"></label><label><span>Alt text <em>For images</em></span><input type="text" name="alt_text" placeholder="Describe the image for accessibility"></label><label class="media-field-wide"><span>Caption <em>Optional</em></span><input type="text" name="caption" placeholder="Short public-facing caption"></label><label class="media-field-wide"><span>Description <em>Optional</em></span><textarea name="description" rows="3" placeholder="Internal context for your team"></textarea></label></div>
        <div class="media-upload-footer"><small>Files are stored securely and can be reused in website and portal content.</small><button class="btn btn-primary" type="submit"><i data-lucide="upload-cloud"></i> Upload asset</button></div>
    </form>
</section>
