<?php
// Library Post Type Class

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ClassLibraryPostType {

    // Plugin Constructor
    public function __construct() {
        add_action('init', [$this, 'create_post_type']);
    }

    // Create Post Type
    public function create_post_type() {
        register_post_type('library', [
            'labels' => [
                'name'          => __('Libraries'),
                'singular_name' => __('Library')
            ],
            'public'        => true,
            'menu_position' => 5,
            'has_archive'   => true,
            'menu_icon'     => 'dashicons-book',
            'show_in_rest'  => true,
            'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
        ]);
    }
}