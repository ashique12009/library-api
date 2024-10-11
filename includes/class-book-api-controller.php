<?php 
// API controller class

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Book API Controller
class ClassBookApiController {

    // Register rest routes
    public function register_rest_routes() {
        // POST endpoint to create a new book with a library association
        register_rest_route('library-api/v1', '/books/', [
            'methods'               => 'POST',
            'callback'              => [$this, 'create_book'],
            'permission_callback'   => [$this, 'check_permission_admin'],
        ]);

        // GET endpoint to retrieve all books for a specific library
        register_rest_route('library-api/v1', '/library/(?P<library_id>\d+)/books/', [
            'methods'               => 'GET',
            'callback'              => [$this, 'get_books_by_library'],
            'permission_callback'   => [$this, 'check_permission_logged_in'],
        ]);
    }

    // Create book
    public function create_book(WP_REST_Request $request) {
        $title      = sanitize_text_field($request['title']);
        $content    = wp_kses_post($request['content']);
        $excerpt    = sanitize_text_field($request['excerpt']);
        $library_id = intval($request['library_id']);

        // Check if the provided library exists
        if (!get_post($library_id) || get_post_type($library_id) !== 'library') {
            return new WP_Error('invalid_library', 'Library not found', ['status' => 404]);
        }

        // Check if a library with the same title already exists
        $existing_book = get_page_by_title($title, OBJECT, 'book');

        if ($existing_book) {
            return new WP_Error('book_already_exists', 'A book with the same title already exists', ['status' => 400]);
        }

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status'  => 'publish',
            'post_type'    => 'book'
        ]);

        if (is_wp_error($post_id)) {
            return new WP_Error('book_creation_failed', 'Failed to create book', ['status' => 500]);
        }

        // Store the library ID as post meta for the book
        update_post_meta($post_id, 'library_id', $library_id);

        return new WP_REST_Response(['book_id' => $post_id, 'message' => 'Book created successfully'], 201);
    }

    // Method to retrieve books for a specific library
    public function get_books_by_library(WP_REST_Request $request) {
        $library_id = intval($request['library_id']);

        // Check if the library exists
        if (!get_post($library_id) || get_post_type($library_id) !== 'library') {
            return new WP_Error('invalid_library', 'Library not found', ['status' => 404]);
        }

        // Query for books belonging to this library
        $args = [
            'post_type'         => 'book',
            'meta_key'          => 'library_id',
            'meta_value'        => $library_id,
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
        ];
        $books = get_posts($args);

        if (empty($books)) {
            return new WP_REST_Response(['message' => 'No books found for this library'], 200);
        }

        return new WP_REST_Response($books, 200);
    }

    // Permission checks...
    public function check_permission_admin() {
        return current_user_can('manage_options');
    }

    public function check_permission_logged_in() {
        return is_user_logged_in();
    }
}