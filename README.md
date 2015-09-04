# wordpress-photo-album
###Wordpress photo album

Photo gallery for wordpress that is relative to vk.com albums

Requirements:
--Wordpress 3.5+
--php 5.4+. Closures and array() = [] used
--Bootstrap

Plugin registers new taxonomy for albums(terms) and new post_type for photos.
There is nothing interesting, but some kind of base requirements to build your own photo gallery with blackjack and ... you know...

Tried to do it as much flexible as possible for me.

Url rewriting system included, so it is manageable by settings in class.
-Rules are declared in class constrcutor.
L18N system included - translations kept in plugin_dir().'/locatization/'.get_locale().'.php'
-Can be called by WordpressPhotoAlbum::__t('some_phrase')
