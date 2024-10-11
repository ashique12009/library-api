<?php 
/*
Plugin Name: Custom library API Plugin
Description: A custom plugin for the library to maintain by API.
Version: 1.0
Author: Khandoker Ashique Mahamud
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Main plugin class
class LibraryAPI {

    // Plugin Constructor
    public function __construct() {
        $this->plugin_constants();
        add_action('init', [$this, 'register_posts_types']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    // Define constants
    public function plugin_constants() {
        define('LIBRARY_API_VERSION', '1.0');
        define('LIBRARY_API_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('LIBRARY_API_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    // Register post types
    public function register_posts_types() {
        require_once LIBRARY_API_PLUGIN_DIR . 'includes/class-library-post-type.php';
        $libraryPostType = new LibraryPostType();
        $libraryPostType->create_post_type();

        require_once LIBRARY_API_PLUGIN_DIR . 'includes/class-book-post-type.php';
        $bookPostType = new ClassBookPostType();
        $bookPostType->create_post_type();
    }

    // Register rest routes
    public function register_rest_routes() {
        require_once LIBRARY_API_PLUGIN_DIR . 'includes/class-library-api-controller.php';
        $apiController = new ClassApiController();
        $apiController->register_rest_routes();

        require_once LIBRARY_API_PLUGIN_DIR . 'includes/class-book-api-controller.php';
        $bookApiController = new ClassBookApiController();
        $bookApiController->register_rest_routes();
    }
}

// Initialize the plugin
$library_api = new LibraryAPI();