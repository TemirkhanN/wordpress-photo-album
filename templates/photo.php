<?php
/*
 *  Template for single photo
 */

$photo = WordpressPhotoAlbum::getPhoto($params['photo_id']);

if(!$photo){
    WordpressPhotoAlbum::redirectTo404();
}

$photoSource = wp_get_attachment_url(get_post_thumbnail_id($params['photo_id']));

?>

<img src="<?=$photoSource?>" alt="<?=$photo->post_title?>" title="<?=$photo->post_title?>">
<br>
<?=$photo->post_excerpt?>
<br>
<br>

<?php comments_template(); ?>