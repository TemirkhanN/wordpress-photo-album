##=== Wordpress vk-photo album ===

**Contributors:** https://profiles.wordpress.org/temirkhan

**Tags:** vk, photos, gallery, album

**Requires at least:** Wordpress 3.5, bootstrap, jquery, php 5.4

**Tested up to:** 4.2.4

**Stable tag:** 4.2

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html


###== Description ==

Basic photo gallery that is familiar to vk.com photo albums.
Plugin registers new taxonomy for albums(terms) and new post_type for photos.
It is fully adaptive(bootstrap grid)

Url rewriting system included, so it is manageable by settings in class.

    Rules are declared in class constrcutor.

L18N system included - translations kept in plugin_dir().'/locatization/' . WordpressPhotoAlbum::LOCALE . '.php'

    Can be called by WordpressPhotoAlbum::__t('some_phrase')



###== Installation ==

* Upload wordpress-photo-album folder to plugins directory
* Activate it through plugins menu in wordpress admin-panel
* By default photoalbum root will be shown at http://yourhost/album/



###== Frequently Asked Questions ==

#####= How can I change default plugin url? =

    For now it can be changed only via editing album value in next files:

    /WordpressPhotoAlbum.php
    * const TAXONOMY_SLUG = 'album';


    /js/wp-photo-album.js
    * this.albumRealUrl = '/album/{albumSlug}/';
    * this.photoRealUrl = '/album/photo-{photoId}/';

#####= Why every photo shall be added separately? =

Because photos shall 
* have comments;
* be easily be fetched(got from database) via wordpress api;
* be easily sorted and listed as other posts

###== Screenshots ==


####Wp-admin albums list
![photoalbum-root](http://paranoia.today/gitpub/img/admin-albums-list.png)


####Adding new photo
![Adding-new-photo](http://paranoia.today/gitpub/img/photo-add.png)


####Album root page
![Album-root-page](http://paranoia.today/gitpub/img/photoalbum-root.png)


####Non-root album with child albums
![Non-root-album-with-child-albums](http://paranoia.today/gitpub/img/album-with-child-albums.png)


####Photos in album
![photoalbum-root](http://paranoia.today/gitpub/img/photos-list.png)


####Popup photo(full page screenshot)
![popup-photo-full](http://paranoia.today/gitpub/img/photo-popup-full-page.png)


####Popup photo(real size)
![popup-photo-real](http://paranoia.today/gitpub/img/photo-popup-real.png)


####Direct link photo
![photo-direct-link](http://paranoia.today/gitpub/img/photo-separate-url.png)
