<div class="media-page">

    <?php require __DIR__ . '/widgets/toolbar.php'; ?>

    <?php if (empty($media)): ?>

        <?php require __DIR__ . '/widgets/empty-state.php'; ?>

    <?php else: ?>

        <?php require __DIR__ . '/widgets/grid.php'; ?>

        <?php require __DIR__ . '/widgets/pagination.php'; ?>

    <?php endif; ?>

    <?php require __DIR__ . '/widgets/upload-modal.php'; ?>

</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.delete-media-form').forEach(function(form){form.addEventListener('submit',function(event){if(!confirm('Move this asset to trash?'))event.preventDefault();});});
    var input=document.querySelector('.media-file-picker input[name="media"]'),preview=document.getElementById('mediaPreview');
    if(!input||!preview)return;
    input.addEventListener('change',function(){preview.replaceChildren();var file=this.files&&this.files[0];if(!file)return;if(file.type.indexOf('image/')!==0){var note=document.createElement('span');note.textContent=file.name+' · '+Math.round(file.size/1024)+' KB';preview.append(note);return;}var image=document.createElement('img');image.alt='Selected file preview';preview.append(image);var reader=new FileReader();reader.onload=function(event){image.src=event.target.result;};reader.readAsDataURL(file);});
    var search=document.querySelector('[data-media-live-search]'),assets=[].slice.call(document.querySelectorAll('[data-media-asset]')),empty=document.querySelector('[data-media-live-empty]'),timer;
    if(search){search.addEventListener('input',function(){var query=search.value.trim().toLowerCase(),matches=0;assets.forEach(function(asset){var match=!query||asset.getAttribute('data-media-searchable').indexOf(query)!==-1;asset.hidden=!match;if(match)matches++;});if(empty)empty.hidden=matches!==0||!query;clearTimeout(timer);timer=setTimeout(function(){search.form.submit();},500);});}
});
</script>
