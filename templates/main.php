<?php


$albums = WordpressPhotoAlbum::getAlbums(0);
status_header(200);

add_filter('wp_title', function(){
    return WordpressPhotoAlbum::__t('menu_name');
});


get_header();


?>

<div class="container">
    <h1 class="album-h1"><?php wp_title();?></h1>
    <div class="row wp-photo-album">
        <?php foreach ($albums as $album): ?>
            <div class="col-md-3 col-sm-4 col-xs-6 album-preview">
                <div class="thumbnail photo-thumb">
                    <a href="<?=WordpressPhotoAlbum::albumUrl($album->slug)?>">
                        <img src="<?=$album->lastPhoto?>" class="centered" title="<?=$album->name?>">

                    </a>
                </div>
                <a href="<?=WordpressPhotoAlbum::albumUrl($album->slug)?>"><?=$album->name?></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer(); ?>
