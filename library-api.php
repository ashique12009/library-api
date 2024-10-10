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

class LibraryAPI {

    // Plugin Constructor
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    // Register rest routes
    public function register_rest_routes() {
        register_rest_route('library/v1', 'library', [
            'methods' => 'GET',
            'callback' => [$this, 'get_library']
        ]);
    }
}

// Initialize the plugin
$library_api = new LibraryAPI();