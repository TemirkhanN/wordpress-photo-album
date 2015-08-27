<?php
/*  Template for album(category) echo. Displays photos
 */

$album = WordpressPhotoAlbum::getAlbumInfo($params['album_name']);

if(!$album){
    WordpressPhotoAlbum::redirectTo404();
}

add_filter('wp_title', function() use($album){
    return $album->name;
});

$page = !empty($params['page'])  ? $params['page'] : 1;


$photos = WordpressPhotoAlbum::getPhotos($album->slug, $page);




// get_header();
?>


<h1><?php wp_title();?></h1><br><br>

<?php if($photos->have_posts()):  ?>
    <?php while($photos->have_posts()): $photos->the_post(); ?>

        <?php if(has_post_thumbnail()): ?>
            <img src="<?=wp_get_attachment_thumb_url(get_post_thumbnail_id(get_the_ID()))?>"
                 alt="<?=get_the_title()?>"
            >
        <?php endif; ?>

    <?php endwhile; ?>
<?php endif;?>

<?php //get_footer(); ?>
