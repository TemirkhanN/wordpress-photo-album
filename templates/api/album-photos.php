<?php


$album = WordpressPhotoAlbum::getAlbum($params['album_slug']);

if(!$album){
    WordpressPhotoAlbum::redirectTo404();
}




$photos = WordpressPhotoAlbum::getPhotos($album->slug, $params['page']);

if($photos->have_posts()){
    status_header(200);
}
?>



<?php if($photos->have_posts()):  ?>
    <?php while($photos->have_posts()): $photos->the_post(); ?>
        <div class="col-md-3 col-sm-4 col-xs-6 photo-preview">
            <div class="thumbnail photo-thumb">
                <?php if(has_post_thumbnail()): ?>
                    <a href="<?=WordpressPhotoAlbum::photoUrl(get_the_ID())?>" title="<?=get_the_title()?>">
                        <img data-target-id="<?=get_the_ID()?>" src="<?=wp_get_attachment_image_src(get_post_thumbnail_id($lastPhotoData->ID), 'medium')[0]?>"
                             alt="<?=get_the_title()?>"
                            >
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif;?>
