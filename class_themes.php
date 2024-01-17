<?php  

if( ! defined("THEME_URL") && ! defined("THEME_PATH") && ! defined("THEME_PATH_CORE") && ! defined("THEME_URL_CORE") ) { exit; }

class LMH_Themes {

    protected $loader;
    protected $text_domain;
    protected $post_types;
    protected $taxonomys;

    public function __construct($text_domain, $post_types, $taxonomys) {

        $this->text_domain = $text_domain;
        $this->post_types = $post_types;
        $this->taxonomys = $taxonomys;

        load_theme_textdomain( $this->text_domain , THEME_PATH . '/language' );

        $this->load_dependencies();

    }

    private function load_dependencies() {

        require_once THEME_PATH_CORE . '/includes/class-theme-loader.php';

        $this->loader = new LMH_Theme_Loader();

        $this->loader->add_action( 'init', $this ,'theme_setup');

        $this->loader->add_action( 'admin_init', $this ,'admin_init', 1);

        $this->loader->add_action( 'wp_enqueue_scripts', $this ,'wp_enqueue_scripts');

        $this->loader->add_action( 'add_meta_boxes', $this ,'add_meta_boxes');
        $this->loader->add_action( 'save_post', $this, 'meta_box_save_post', 100, 3 );

        $this->loader->add_action( 'template_redirect', $this ,'disable_author_page');

        $this->loader->add_action( 'postviews_increment_views', $this ,'postviews_increment_views');
        $this->loader->add_action( 'postviews_increment_views_ajax', $this ,'postviews_increment_views');
        
        $this->loader->add_filter( 'wp_head' , $this ,'wp_head' , 10 );
        $this->loader->add_filter( 'wp_title', $this , 'wp_title');
        $this->loader->add_filter( 'show_admin_bar', $this, 'show_admin_bar');
        $this->loader->add_filter( 'sanitize_file_name', $this,'file_renamer', 10, 1 );
        $this->loader->add_filter( 'post_thumbnail_html', $this, 'remove_width_height', 10 );
        $this->loader->add_filter( 'image_send_to_editor', $this, 'remove_width_height', 10 );
        $this->loader->add_filter( 'excerpt_length', $this, 'excerpt_length' , 999);
        $this->loader->add_filter( 'sanitize_title', $this, 'sanitize_title_vn');
        $this->loader->add_filter( 'pre_get_posts', $this, 'pre_get_posts');

    }

    public function run() {
        $this->loader->run();
    }

    public function wp_enqueue_scripts(){

        wp_enqueue_style( $this->text_domain . "_bootstrap" , THEME_URL . "/css/bootstrap.min.css" , array() , '' , 'all' );
        wp_enqueue_style( $this->text_domain . "_font-awesome" , THEME_URL . "/css/font-awesome.min.css" , array() , '' , 'all');
        wp_enqueue_style( $this->text_domain . "_elegant-icons" , THEME_URL . "/css/elegant-icons.css" , array() , '' , 'all');
        wp_enqueue_style( $this->text_domain . "_plyr" , THEME_URL . "/css/plyr.css");
        wp_enqueue_style( $this->text_domain . "_nice-select" , THEME_URL . "/css/nice-select.css" , array() , '' , 'all');
        wp_enqueue_style( $this->text_domain . "_owl.carousel" , THEME_URL . "/css/owl.carousel.min.css" , array() , '' , 'all');
        wp_enqueue_style( $this->text_domain . "_slicknav" , THEME_URL . "/css/slicknav.min.css" , array() , '' , 'all');

        wp_enqueue_style( $this->text_domain . "_style" , THEME_URL . "/css/style.css" , array() , time() , 'all' );
        wp_enqueue_style( $this->text_domain . "_style-main" , THEME_URL . "/style.css" , array() , time() , 'all' );

    }

    public function show_admin_bar() {
        if( ! (current_user_can('administrator') || current_user_can('editor')) ) {
            return false;
        }
        return true;
    }

    public function wp_title(){
        if( is_home() ){
            return get_bloginfo( "name" ) . " | " . get_bloginfo( "description" );    
        }
        return get_the_title();
    }

    function postviews_increment_views() {
        global $post;
        if( ! empty ($post->post_parent) && ($post->post_type == "stories") ){
            
            if ( ! $post_views = get_post_meta($post->post_parent, 'views', true ) ) {
				$post_views = 0;
			}
            update_post_meta( $post->post_parent, 'views', $post_views + 1 );
        }
    }

    public function admin_init(){
        if( ! (current_user_can('administrator') || current_user_can('editor')) ) {
            wp_redirect( '/' ); exit;
        }
    }

    public function wp_head(){
        global $lmh_opt;
        if( isset($lmh_opt['favicon']) && ! empty($lmh_opt['favicon']['url']) ) {
            echo "<link rel='shortcut icon' type='image/png' href='{$lmh_opt['favicon']['url']}' />";
        } else {
            echo "<link rel='shortcut icon' type='image/png' href='" . THEME_URL_CORE . "/images/logo-lmh.jpg' />";
        }
    }

    public function disable_author_page() {
        global $wp_query;
        if ( is_author() ) { wp_redirect(get_option('home'), 301); exit; }
    }

    public function file_renamer( $filename ) {
        $info = pathinfo( $filename );
        $ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
        $name = basename( $filename, $ext );
    
        if( $post_id = array_key_exists("post", $_GET) ? $_GET["post"] : null) {
            if($post = get_post($post_id)) {
                return sanitize_title($post->post_title). '-' . time() . $ext;
            }
        }
    
        if( $post_id = array_key_exists("post_id", $_POST) ? $_POST["post_id"] : null) {
            if($post = get_post($post_id)) {
                return sanitize_title($post->post_title). '-' . time() . $ext;
            }
        }
        return $filename;
    }

    public function remove_width_height( $html ) {
        $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
        return $html;
    }

    public function sanitize_title_vn( $str ) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        return $str;
    }

    public function excerpt_length( $length ) {
        return 20;
    }
    
    public function pre_get_posts( $query ){

        if ( is_admin() ) { return; }
        
        if ( ( is_home() || is_search() ) && $query->is_main_query()  ) {
            $query->set('post_type', array('story') );
        }

    }

    public function edit_story_columns( $columns ) {
        global $post;
        $columns['stories'] = __('Stories', $this->text_domain);
        return $columns;
    }

    public function custom_story_column( $column, $post_id ){
        global $wpdb;
        switch ( $column ) {
            case 'stories' : 
                $stories = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE `post_parent` = '{$post_id}' AND `post_type` = 'stories'");
                echo ($stories ?? "0");
            break;
        }
    }

    public function edit_stories_columns( $columns ) {
        global $post;
        $columns['story'] = __('Story', $this->text_domain);
        return $columns;
    }

    public function custom_stories_column( $column, $post_id ){
        switch ( $column ) {
            case 'story' : 
                $story = get_post_parent($post_id);
                echo ($story->post_title ?? "");
            break;

        }
    }

    public function add_meta_boxes() {
        $screen = get_current_screen();
        if( $screen->post_type == "stories" ){
            add_meta_box( "stories-info" , __( 'Stories Info', $this->text_domain ), [ $this, "meta_box_html_stories" ] , $screen->post_type );
        }
    }

    public function meta_box_html_stories( $post ) {
        $story = get_posts( [
            "post_type" => "story",
            "numberposts" => 10,
        ]);

        ?>
            <div>
                <label> <?php echo __("Select Story: ", $this->text_domain);?></label>
                <select class="select2" name="story_parent" id="story_parent">
                    <?php 
                        echo "<option value='0' ". selected(0,$post->post_parent,false ) ." >--- Vui lòng chọn truyện ---</option>";
                        foreach ($story as $val) {
                            echo "<option value='{$val->ID}' ". selected($val->ID,$post->post_parent,false) .">{$val->post_title}</option>";
                        }
                    ?>
                </select> 
            </div>
        <?php
        
        wp_enqueue_style( "select2" , "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" );
        wp_enqueue_script( "select2", "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" , [] ,'' , true );
        wp_enqueue_script( "lmh-select2", THEME_URL_CORE . "/js/metabox.js" , [] , time() , true );

    }

    public function meta_box_save_post( $post_id, WP_Post $post, $update ) {
        global $wpdb;
        if( isset($_POST['story_parent']) ) {
            $wpdb->update(
                "{$wpdb->prefix}posts",
                [ 'post_parent' => sanitize_key( $_POST['story_parent'] ) ],
                [ 'ID' => $post_id ]
            ); 

            $wpdb->update(
                "{$wpdb->prefix}posts",
                [ 'post_modified' => date('Y-m-d H:i:s') ],
                [ 'ID' => sanitize_key( $_POST['story_parent'] ) ]
            );
        }
    }

    public function theme_setup(){

        add_theme_support( 'post-thumbnails');
        register_nav_menu( 'primary-menu' , __('Primary Menu', $this->text_domain ) );
        register_nav_menu( 'second-menu' , __('Second Menu', $this->text_domain ) );
        
        $sidebar = array(
            'name' => __('Main Sidebar', $this->text_domain ),
            'id'    => 'main-sidebar',
            'class' => 'main-sidebar',
            'before_title' => '<h3>',
            'after_title'  => '</h3>',
        );
        register_sidebar( $sidebar );

        if( ! empty($this->post_types) ){
            foreach( $this->post_types as $k=>$pt ) {
                register_post_type( $k ,$pt );
            }
        }

        if( ! empty($this->taxonomys) ){
            foreach( $this->taxonomys as $tax ) {
                register_taxonomy( $tax['taxonomy'], $tax['post_type'] , $tax['args'] );
            }
        }

        add_filter( 'manage_stories_posts_columns', [$this, 'edit_stories_columns'] );
        add_action( 'manage_stories_posts_custom_column' , [$this, 'custom_stories_column'] , 10, 2 );

        add_filter( 'manage_story_posts_columns', [$this, 'edit_story_columns'] );
        add_action( 'manage_story_posts_custom_column' , [$this, 'custom_story_column'] , 10, 2 );

    }
    
}