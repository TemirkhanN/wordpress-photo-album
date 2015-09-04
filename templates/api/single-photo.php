<?php
/*
 *  Template for API photo
 */


$photo = WordpressPhotoAlbum::getPhoto($params['photo_id']);
if($photo):

    status_header(200);
    ?>

    <?=WordpressPhotoAlbum::__t('singular_name') .' '. $photo->additionalInfo->position . ' ' . WordpressPhotoAlbum::__t('of') . ' ' .$photo->albumInfo->count?>
    <span class="photo-close" title="<?=WordpressPhotoAlbum::__t('close') .' ' .WordpressPhotoAlbum::__t('photo')?>"><?=WordpressPhotoAlbum::__t('close')?></span>
    <br>
        <img
            class="photo-item"
            src="<?=$photo->photoSource?>"
            alt="<?=$photo->post_title?>"
            title="<?=$photo->post_title?>"
            >
        <br>
    <?=$photo->post_excerpt?>
        <br>
        <a href="<?=$photo->additionalInfo->prev?>"
           id="previous-photo"
           data-target-id="<?=$photo->additionalInfo->prevId?>">Предыдущая</a>
        <a href="<?=$photo->additionalInfo->next?>"
           id="next-photo"
           data-target-id="<?=$photo->additionalInfo->nextId?>">Следующая</a>
        <a href="<?=WordpressPhotoAlbum::albumUrl($photo->albumInfo->slug)?>"
           title="<?=WordpressPhotoAlbum::__t('photo_album') . ' ' .$photo->albumInfo->name?>">
            <?=$photo->albumInfo->name?>
        </a>
        <br>

    <?php comments_template(); ?>
<?php endif; ?>
