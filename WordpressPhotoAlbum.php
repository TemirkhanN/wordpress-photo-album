<?php



class WordpressPhotoAlbum
{
    const TPL_DIR = 'templates';
    const POST_TYPE = 'wp-photo';
    const TAXONOMY = 'photo_albums';
    const TAXONOMY_SLUG = 'album';

    public static $urlRules = [];
    public static $hideEmptyAlbums = false;
    public static $photosOnPage = 16;
    public static $apiUrl = 'PHOTO_ALBUM_API';
    private static $localization;
    private static $photosOrder = ['order_by'=>'modified', 'order' => 'DESC'];




    public static function init()
    {
        self::$urlRules = [
            //Base gallery page
            'main' => [
                'rule' => '^/'.self::TAXONOMY_SLUG.'/?$',
                'params' => [],
                'template' => 'main',
            ],
            //Photo page
            'photo' => [
                'rule' => '^/'.self::TAXONOMY_SLUG.'/photo-(\d+)/$',
                'params'=> [
                    'photo_id'
                ],
                'template' => 'photo',
            ],
            // Album page
            'album' => [
                'rule' => '^/'.self::TAXONOMY_SLUG.'/([a-zA-Z\-_0-9]{3,})/$',
                'params' => [
                    'album_slug',
                    'junk_var',
                    'page'
                ],
                'template' => 'album',
            ],
            //API photo get
            [
                'rule' => '^/'.self::$apiUrl.'/photo-(\d+)/$',
                'params' => [
                    'photo_id',
                ],
                'template'=>'api/single-photo'
            ],
            //API album photos get
            [
                'rule' => '^/'.self::$apiUrl.'/album-([a-zA-Z\-_0-9]{3,})?/page-(\d+)/$',
                'params' => [
                    'album_slug',
                    'page'
                ],
                'template' => 'api/album-photos'
            ]
        ];

        self::registerPhotoPostType();
        self::registerUrlRewrite();
        self::registerPostFilter();
    }


    public static function uninstall()
    {
        global $wpdb;

        $wpdb->delete('wp_posts', ['post_type'=>self::POST_TYPE]);
    }



    public static function getAlbums($parentAlbum = '', $withLastPhoto = false)
    {
        $albums = get_terms(
            self::TAXONOMY,
            [
                'hide_empty' => self::$hideEmptyAlbums,
                'orderby' => 'name',
                'order' => 'ASC',
                'parent' => $parentAlbum,

            ]
        );

        //Tis absolutely non-sense, but I don't see another way to get it through wp-api
        if($albums){
            foreach($albums as $key=>$album){
                $albums[$key]->lastPhoto = self::getLastAlbumPhotoThumb($album->slug);
            }
        }

        return $albums;
    }



    private static function getLastAlbumPhotoThumb($albumSlug)
    {
        $lastPhoto = self::getPhotos($albumSlug, 0, '', 1);
        if($lastPhoto->have_posts()){
            $lastPhotoData = array_shift($lastPhoto->get_posts());
            $photo = wp_get_attachment_image_src(get_post_thumbnail_id($lastPhotoData->ID), 'medium');
            if($photo){
                $photo = $photo[0];
            }
        } else{
            $photo = plugins_url('/css/image-folder.png', __FILE__);
        }

        return $photo;
    }


    public static function getAlbum($albumSlug)
    {
        return get_term_by('slug', $albumSlug, self::TAXONOMY);
    }


    public static function getPhotos($albumSlug, $page = 1, $fields = '', $limit = null)
    {
        $page = (int)$page > 0 ? (int)$page : 1;
        $postsPerPage = $limit === null ? self::$photosOnPage : (int)$limit;

        return new WP_Query([
            'post_type' => self::POST_TYPE,
            'posts_per_page' => $postsPerPage,
            'post_status' => 'publish',
            'offset' => self::$photosOnPage * ($page-1),
            'orderby' => self::$photosOrder['order_by'],
            'order' => self::$photosOrder['order'],
            'fields' => $fields,
            'tax_query' => [
                [
                    'taxonomy' => self::TAXONOMY,
                    'field' => 'slug',
                    'terms' => $albumSlug,
                ]

            ],

        ]);
    }



    public static function getPhoto($id)
    {
        $id = (int)$id;

        if($id<=0){
            return false;
        }

        $GLOBALS['wp_query']->is_single = true;
        $GLOBALS['post'] = get_post($id);
        if($GLOBALS['post']) {
            $photoSource = wp_get_attachment_url(get_post_thumbnail_id($id));

            $GLOBALS['post']->albumInfo = array_shift(wp_get_object_terms($id, self::TAXONOMY));
            $GLOBALS['post']->photoSource = $photoSource;
            $GLOBALS['post']->additionalInfo = self::getPhotoAdditionalInfo($id, $GLOBALS['post']->albumInfo->slug);
        }

        return $GLOBALS['post'];
    }


    /**
     * Information about photo's position in album, next photo and previous photo
     * link to previous and next photo
     * @param $photoId
     * @param $albumSlug
     * @return int|string
     */
    public static function getPhotoAdditionalInfo($photoId, $albumSlug)
    {
        $photoInfo = new stdClass();
        $photos = self::getPhotos($albumSlug, 1, 'ids', -1);
        if($photos) {
            $photos = $photos->get_posts();
            foreach ($photos as $position => $photo) {
                if ($photo === $photoId) {
                    $photoInfo->position = $position+1;

                    $nextId = isset($photos[$position+1]) ? $photos[$position+1] : ($position!==0 ? $photos[0] : false);
                    $prevId = isset($photos[$position-1]) ? $photos[$position-1] : ($position!==count($photos)-1 ? $photos[count($photos)-1] : false);
                    $photoInfo->next = $nextId!==false ? self::photoUrl($nextId) : false;
                    $photoInfo->prev = $prevId!==false ? self::photoUrl($prevId) : false;
                    $photoInfo->nextId = $nextId;
                    $photoInfo->prevId = $prevId;
                    break;
                }
            }
        }
        return $photoInfo;
    }


    private static function getTplPath()
    {
        return __DIR__ . '/' . self::TPL_DIR .'/';
    }


    public static function albumUrl($albumSlug)
    {
        $urlRule = $albumSlug === '' ? str_replace('?', '', (self::$urlRules['main']['rule'])) : (self::$urlRules['album']['rule']);

        $rule = str_replace(['^', '$'], '', $urlRule);
        return preg_replace('#\((.+)?\)#', $albumSlug, $rule);
    }

    public static function photoUrl($photoId)
    {
        $rule = str_replace(['^', '$'], '', self::$urlRules['photo']['rule']);
        return preg_replace('#\(\\\d\+\)#i', $photoId, $rule);
    }


    public static function __t($word, $lowercase = false)
    {
        if(self::$localization === null ){
            $lcpath = __DIR__.'/localization/' . get_locale() .'.php';
            self::$localization = file_exists($lcpath) ? require $lcpath : array();
        }

        $translated = isset(self::$localization[$word]) ? self::$localization[$word] : str_replace('_', ' ', $word);
        return $lowercase ? lcfirst($translated) : $translated;
    }


    public static function redirectTo404()
    {
        $GLOBALS['wp_query']->set_404();
        status_header( 404 );
        get_template_part( 404 );
        exit();
    }




    private static function registerUrlRewrite()
    {
        foreach(self::$urlRules as $rewrite){
            if(preg_match('#'.$rewrite['rule'].'#is', $_SERVER['REQUEST_URI'], $info)){
                array_shift($info);
                $params = [];
                if(!empty($info)){
                    foreach($info as $key=>$value){
                        $params[$rewrite['params'][$key]] = $value;
                    }
                }

                self::registerJsAndCss(); //Include script and sylesheet only when photo-album is requested
                add_filter('template_include', function () use ($rewrite, $params) {
                    include self::getTplPath() . str_replace('..', '', $rewrite['template']) . '.php';
                });
                break;

            }
        }
    }



    private static function registerJsAndCss()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('', plugins_url('/js/wp-photo-album.js', __FILE__));
        wp_enqueue_style('', plugins_url('/css/wp-photo-album.css', __FILE__));
    }



    private static function registerPhotoPostType()
    {

        add_action( 'init', function () {
            register_taxonomy(
                self::TAXONOMY,
                'albums',
                array(
                    'hierarchical' => true,
                    'show_admin_column' => true,
                    'labels' => [
                        'name' => self::__t('photo_albums'),
                        'add_new_item' => self::__t('add_new_album'),
                    ],
                    'query_var' => true,
                    'rewrite' => array(
                        'slug' => self::TAXONOMY_SLUG,
                        'with_front' => false
                    )
                )
            );
        });


        add_action('init', function(){
            $labels = [
                'name' => self::__t('name'),
                'singular_name' => self::__t('singular_name'),
                'menu_name' => self::__t('menu_name'),
                'name_admin_bar' => self::__t('name_admin_bar'),
                'all_items' => self::__t('all_items'),
                'add_new' => self::__t('add_new'),
                'add_new_item' => self::__t('add_new_item'),
                'edit_item' => self::__t('edit_item'),
                'new_item' => self::__t('new_item'),
                'view_item' => self::__t('view_item'),
                'search_items' => self::__t('search_items'),
                'not_found' => self::__t('not_found'),
            ];

            $args = [
                'labels' => $labels,
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_icon' => 'dashicons-images-alt',
                'menu_position' => 22,
                'supports' => [
                    'title',
                    'thumbnail',
                    'excerpt',
                    'author',
                    'comments',
                ],
                'taxonomies' => [
                    'post_tag',
                    self::TAXONOMY,
                ]
            ];

            register_post_type( WordpressPhotoAlbum::POST_TYPE, $args );
            $currentSupport = get_theme_support('post-thumbnails');
            add_theme_support('post-thumbnails', array_merge($currentSupport[0],[WordpressPhotoAlbum::POST_TYPE]));
        });


        add_action('admin_menu', function(){
            remove_submenu_page('edit.php?post_type=wp-photo', 'edit-tags.php?taxonomy=post_tag&amp;post_type=wp-photo');
        });
    }


    /**
     * Shows filter on posts list admin page
     */
    private static function registerPostFilter()
    {
        add_action( 'restrict_manage_posts', function () {
            global $typenow;
            if( $typenow == self::POST_TYPE){
                $terms = get_terms(self::TAXONOMY);
                echo '<select name="'.self::TAXONOMY.'" id="'.self::TAXONOMY.'" class="postform">';
                echo '<option value="">'. self::__t('sort_by_album').'</option>';
                foreach ($terms as $term) {
                    echo '<option value="'. $term->slug .'" ', $_GET[self::TAXONOMY] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
                }
                echo "</select>";

            }
        });

    }



}