<?php

if( ! defined("THEME_URL") && ! defined("THEME_PATH") && ! defined("THEME_PATH_CORE") && ! defined("THEME_URL_CORE") ) { exit; }

require_once THEME_PATH_CORE . "/class_themes.php";

$text_domain = "theme-anime";

/*
    $post_types is Array
    function: https://developer.wordpress.org/reference/functions/register_post_type/
    Key: $post_type
    Value: args of register_post_type
*/

$post_types = [
    'story' => [
        'labels' => array(
            'name' => __('Story',$text_domain),
            'singular_name' => __('Story',$text_domain),
            'menu_name' => __('Story',$text_domain),
            'add_new' => __( 'Add New',$text_domain),
            'add_new_item' => __( 'Add New Story',$text_domain),
            'new_item' => __( 'New Story',$text_domain),
            'edit_item' => __( 'Edit Story',$text_domain),
            'view_item' => __( 'View Story',$text_domain),
            'all_items' => __( 'All Storys',$text_domain),
            'search_items' => __( 'Search Storys',$text_domain),
        ),
        'rewrite' => array( 'slug' => 'story' ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        'menu_icon' => 'dashicons-book',
    ],
    'stories' => [
        'labels' => array(
            'name'          => __('Stories',$text_domain),
            'singular_name' => __('Stories',$text_domain),
            'menu_name' => __('Stories',$text_domain),
            'add_new' => __( 'Add New',$text_domain),
            'add_new_item' => __( 'Add New Stories',$text_domain),
            'new_item' => __( 'New Stories',$text_domain),
            'edit_item' => __( 'Edit Stories',$text_domain),
            'view_item' => __( 'View Stories',$text_domain),
            'all_items' => __( 'All Stories',$text_domain),
            'search_items' => __( 'Search Stories',$text_domain),
        ),
        'rewrite' => array( 'slug' => 'stories' ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'author',  'comments' ),
        'menu_icon' => "dashicons-portfolio",
    ],
];

/*
    $taxonomys is Array
    function: https://developer.wordpress.org/reference/functions/register_taxonomy/
    taxonomy: name Taxonomy
    post_type: name Post_type
    args: $args Array of register_taxonomy
*/

$taxonomys = [
    [
        "taxonomy" => "types",
        "post_type" => "story",
        "args" => array(
            'label'        => __( 'Types', $text_domain),
            'rewrite'      => array('slug' => 'types'),
            'hierarchical' => true,
        )
    ]
];

if( class_exists("LMH_Themes") ){
    $lmh_theme = new LMH_Themes( $text_domain, $post_types, $taxonomys );
    $lmh_theme->run();
}