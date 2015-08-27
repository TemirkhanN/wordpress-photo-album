<?php


$albums = WordpressPhotoAlbum::getAlbums();

add_filter('wp_title', function(){
    return WordpressPhotoAlbum::__t('menu_name');
});


get_header();
?>


<h1><?php wp_title();?></h1>
<ul>
    <?php
    foreach ($albums as $album) {
        echo '<li>' . '<a href="' . esc_attr(get_term_link($album, WordpressPhotoAlbum::TAXONOMY)) . '" title="' . sprintf( __( "View all photos in %s" ), $album->name ) . '" ' . '>' . $album->name.'</a></li>';
    }
    ?>
</ul>

<?php get_footer(); ?>
