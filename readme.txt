=== vk photo album ===
Contributors: temirkhan
Donate link: 
Tags: vk, photos, gallery, album, images
Requires at least: 3.5
Tested up to: 4.2.4
Stable tag: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Basic photo gallery that is familiar to vk.com photo albums. Plugin registers new taxonomy for albums and new post_type for photos. Design is adaptive

== Description ==

Basic photo gallery that is familiar to vk.com photo albums. Plugin registers new taxonomy for albums(terms) and new post_type for photos. It is fully adaptive(bootstrap grid)

Url rewriting system included, so it is manageable by settings in class.

Rules are declared in class constrcutor.

L18N system included - translations kept in plugin_dir().'/locatization/' . WordpressPhotoAlbum::LOCALE . '.php'

Can be called by WordpressPhotoAlbum::__t('some_phrase')

== Installation ==


1)Upload wordpress-photo-album folder to plugins directory
2)Activate it through plugins menu in wordpress admin-panel
3)By default photoalbum root will be shown at http://yourhost/album/

== Frequently asked questions ==

= How can I change default plugin url? =

For now it can be changed only via editing album value in next files:

/WordpressPhotoAlbum.php
* const TAXONOMY_SLUG = 'album';


/js/wp-photo-album.js
* this.rootUrl = '/album/';

= Why every photo shall be added separately? =

Because photos shall

    have comments;
    be easily be fetched(got from database) via wordpress api;
    be easily sorted and listed as other posts

== Screenshots ==

1. https://camo.githubusercontent.com/39580ad368001506c02ec9717c0ff154966dd280/687474703a2f2f706172616e6f69612e746f6461792f6769747075622f696d672f616c62756d2d776974682d6368696c642d616c62756d732e706e67
2. https://camo.githubusercontent.com/3f7fcf31f0a46c2e19e1baf0b8169d6b99ea0d44/687474703a2f2f706172616e6f69612e746f6461792f6769747075622f696d672f70686f746f732d6c6973742e706e67
3. https://camo.githubusercontent.com/740fc4b282e32916ef2e5bb2b54aea89513529b6/687474703a2f2f706172616e6f69612e746f6461792f6769747075622f696d672f70686f746f2d706f7075702d66756c6c2d706167652e706e67

== Changelog ==



== Upgrade notice ==



== Arbitrary section 1 ==
