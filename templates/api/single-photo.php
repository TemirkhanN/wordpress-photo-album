<?php
/*
 *  Template for API photo
 */


$photo = WordpressPhotoAlbum::getPhoto($params['photo_id']);
if($photo):

    status_header(200);
    ?>


    <?php if(is_admin_bar_showing()):?>
        <style>
            .popup-photo {
                top:25px;
            }
        </style>
    <?php endif; ?>


    <div class="photo-summary">
        <span class="photo-position">
            <?=WordpressPhotoAlbum::__t('singular_name') .' '. $photo->additionalInfo->position . ' ' . WordpressPhotoAlbum::__t('of') . ' ' .$photo->albumInfo->count?>
        </span>
        <span
            class="photo-close"
            title="<?=WordpressPhotoAlbum::__t('close') .' ' .WordpressPhotoAlbum::__t('photo')?>">
            <?=WordpressPhotoAlbum::__t('close')?>
        </span>
    </div>
    <div class="photo-item-parent">
        <img
            class="photo-item"
            src="<?=$photo->photoSource?>"
            alt="<?=$photo->post_title?>"
            title="<?=$photo->post_title?>"
        >
    </div>
    <a href="<?=$photo->additionalInfo->prev?>"
       id="previous-photo"
       data-target-id="<?=$photo->additionalInfo->prevId?>"><?=WordpressPhotoAlbum::__t('previous')?>
    </a>
    <a href="<?=$photo->additionalInfo->next?>"
       id="next-photo"
       data-target-id="<?=$photo->additionalInfo->nextId?>"><?=WordpressPhotoAlbum::__t('next')?>
    </a>
    <div class="row photo-additional-info">
        <div class="col-xs-9">
            <div class="photo-description">
                <?=$photo->post_excerpt?>
            </div>
            <div class="photo-pubdate">
                <?=WordpressPhotoAlbum::__t('added')?> <?=date("j F Y", strtotime($photo->post_date))?>
            </div>

            <?php comments_template(); ?>

        </div>
        <div class="col-xs-3">
            <a href="<?=WordpressPhotoAlbum::albumUrl($photo->albumInfo->slug)?>"
               title="<?=WordpressPhotoAlbum::__t('photo_album') . ' ' .$photo->albumInfo->name?>">
                <?=$photo->albumInfo->name?>
            </a>
        </div>
    </div>


<?php endif; ?>
