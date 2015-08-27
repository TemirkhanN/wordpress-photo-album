<?php

class WordpressPhotoAlbum
{
    const TPL_DIR = 'templates';
    const POST_TYPE = 'wp-photo';
    const TAXONOMY = 'photo_albums';
    const TAXONOMY_SLUG = 'album';

    public static $urlRules = [];
    public static $showEmptyAlbums = true;
    public static $photosOnPage = 16;
    public static $apiUrl = '/PHOTO_ALBUM_API/';
    private static $localization;




    public static function init()
    {
        self::$urlRules = [
            //Base gallery page
            [
                'rule' => '^/'.self::TAXONOMY_SLUG.'/?$',
                'params' => [],
                'template' => 'main',
            ],
            // Album page
            [
                'rule' => '^/'.self::TAXONOMY_SLUG.'/([a-zA-Z\-_]{3,})/(page_(\d+))*$',
                'params' => [
                    'album_name',
                    'junk_var',
                    'page'
                ],
                'template' => 'album',
            ],
            //Photo page
            [
                'rule' => '^/'.self::TAXONOMY_SLUG.'/photo-(\d+)/$',
                'params'=> [
                    'photo_id'
                ],
                'template' => 'photo',
            ],
            //API
            [
                'rule' => '^/'.self::$apiUrl.'/',
                'template'=>'api'
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



    public static function getAlbums()
    {
        return get_terms(
            self::TAXONOMY,
            [
                'hide_empty'=>self::$showEmptyAlbums,
            ]
        );
    }


    public static function getAlbumInfo($albumSlug)
    {
        return get_term_by('slug', $albumSlug, WordpressPhotoAlbum::TAXONOMY);
    }


    public static function getPhotos($albumSlug, $page = 1)
    {
        $page = (int)$page > 0 ? (int)$page : 1;

        return new WP_Query([
            'post_type' => WordpressPhotoAlbum::POST_TYPE,
            'posts_per_page' => WordpressPhotoAlbum::$photosOnPage,
            'offset' => WordpressPhotoAlbum::$photosOnPage * ($page-1),
            'tax_query' => [
                [
                    'taxonomy' => WordpressPhotoAlbum::TAXONOMY,
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

        return $GLOBALS['post'];
    }


    private static function getTplPath()
    {
        return __DIR__ . '/' . self::TPL_DIR .'/';
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
                add_filter('template_include', function () use ($rewrite, $params) {
                    include self::getTplPath() . str_replace('..', '', $rewrite['template']) . '.php';
                });

            }
        }
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
                        'name' => self::__t('photo_album'),
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
            add_theme_support('post-thumbnails', [WordpressPhotoAlbum::POST_TYPE]);
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