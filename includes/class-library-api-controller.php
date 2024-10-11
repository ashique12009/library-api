<?php 
// API controller class

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Library API Controller
class ClassLibraryApiController {

    // Register rest routes
    public function register_rest_routes() {
        // Register routes for custom post types for Library
        register_rest_route('library-api/v1', '/libraries/', [
            'methods'               => 'GET',
            'callback'              => [$this, 'get_libraries'],
            'permission_callback'   => [$this, 'check_permission_logged_in'],
        ]);

        register_rest_route('library-api/v1', '/library/', [
            'methods'               => 'POST',
            'callback'              => [$this, 'create_library'],
            'permission_callback'   => [$this, 'check_permission_admin'],
        ]);
    }

    // Get libraries
    public function get_libraries(WP_REST_Request $request) {
        $args = [
            'post_type'      => 'library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];
        $libraries = get_posts($args);
        
        return new WP_REST_Response($libraries, 200);
    }

    // Loggedin permissions check
    public function check_permission_logged_in() {
        return is_user_logged_in();
    }

    // Create library
    public function create_library(WP_REST_Request $request) {
        $title      = sanitize_text_field($request['title']);
        $content    = wp_kses_post($request['content']);
        $excerpt    = sanitize_text_field($request['excerpt']);

        // Check if a library with the same title already exists
        $existing_library = get_page_by_title($title, OBJECT, 'library');

        if ($existing_library) {
            return new WP_Error('library_already_exists', 'A library with the same title already exists', ['status' => 400]);
        }

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status'  => 'publish',
            'post_type'    => 'library'
        ]);

        if (is_wp_error($post_id)) {
            return new WP_Error('library_creation_failed', 'Failed to create library', ['status' => 500]);
        }

        return new WP_REST_Response(['library_id' => $post_id, 'message' => 'Library created successfully'], 201);
    }

    // Loggedin permissions check
    public function check_permission_admin() {
        return current_user_can('manage_options');
    }
}