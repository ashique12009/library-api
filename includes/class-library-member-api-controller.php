<?php 
// API controller class

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Book API Controller
class ClassLibraryMemberApiController {

    // Register rest routes
    public function register_rest_routes() {
        // POST endpoint to create a library member with a library association
        register_rest_route('library-api/v1', '/library-member/', [
            'methods'               => 'POST',
            'callback'              => [$this, 'create_library_member'],
            'permission_callback'   => [$this, 'check_permission_admin'],
        ]);

        // GET endpoint to retrieve all library members for a specific library
        register_rest_route('library-api/v1', '/library/(?P<library_id>\d+)/library-members/', [
            'methods'               => 'GET',
            'callback'              => [$this, 'get_library_members_by_library'],
            'permission_callback'   => [$this, 'check_permission_logged_in'],
        ]);

        // POST endpoint to issue a book from a library
        register_rest_route('library-api/v1', '/issue-book/(?P<library_id>\d+)/(?P<library_member_id>\d+)/(?P<book_id>\d+)/', [
            'methods'               => 'POST',
            'callback'              => [$this, 'issue_book'],
            'permission_callback'   => [$this, 'check_permission_admin'],
        ]);

        // POST endpoint to return a book to a library
        register_rest_route('library-api/v1', '/return-book/(?P<library_id>\d+)/(?P<library_member_id>\d+)/(?P<book_id>\d+)/', [
            'methods'               => 'POST',
            'callback'              => [$this, 'return_book'],
            'permission_callback'   => [$this, 'check_permission_admin'],
        ]);
    }

    // Create library member
    public function create_library_member(WP_REST_Request $request) {
        $library_id   = intval($request['library_id']);
        $username     = sanitize_text_field($request['username']);
        $first_name   = sanitize_text_field($request['first_name']);
        $last_name    = sanitize_text_field($request['last_name']);
        $email        = sanitize_email($request['email']);
        $password     = $request['password'];

        if (username_exists($username) || email_exists($email)) {
            return new WP_Error('user_exists', 'User already exists', ['status' => 400]);
        }

        // Check if the library exists
        if (!get_post($library_id) || get_post_type($library_id) !== 'library') {
            return new WP_Error('invalid_library', 'Library not found', ['status' => 404]);
        }

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return new WP_Error('user_creation_failed', 'Failed to create user', ['status' => 500]);
        }

        wp_update_user(['ID' => $user_id, 'role' => 'library-member']);
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'library_id', $library_id);

        return new WP_REST_Response(['user_id' => $user_id, 'message' => 'Library member created successfully'], 201);
    }

    // Callback for getting all library members
    public function get_library_members_by_library(WP_REST_Request $request) {
        $library_id = intval($request['library_id']);

        // Check if the library exists
        if (!get_post($library_id) || get_post_type($library_id) !== 'library') {
            return new WP_Error('invalid_library', 'Library not found', ['status' => 404]);
        }

        $args = [
            'role'          => 'library-member',
            'meta_key'      => 'library_id',
            'meta_value'    => $library_id
        ];
        $users = get_users($args);

        return new WP_REST_Response($users, 200);
    }

    // Issue a book from a library
    public function issue_book(WP_REST_Request $request) {
        $library_id         = intval($request['library_id']);
        $book_id            = intval($request['book_id']);
        $library_member_id  = intval($request['library_member_id']);

        // Check if the library exists
        if (!get_post($library_id) || get_post_type($library_id) !== 'library') {
            return new WP_Error('invalid_library', 'Library not found', ['status' => 404]);
        }
        
        // Check if the book exists
        if (!get_post($book_id) || get_post_type($book_id) !== 'book') {
            return new WP_Error('invalid_book', 'Book not found', ['status' => 404]);
        }

        // Check if the library member exists
        if (!get_user_by('ID', $library_member_id)) {
            return new WP_Error('invalid_library_member', 'Library member not found', ['status' => 404]);
        }

        // Check if the book is already issued to the library member
        $issued_book = get_user_meta($library_member_id, 'issued_book_id', true);
        if ($issued_book) {
            return new WP_Error('book_already_issued', 'Book already issued', ['status' => 400]);
        }

        // Update the library member metadata
        update_user_meta($library_member_id, 'issued_library_id_' . $library_id, $library_id);
        update_user_meta($library_member_id, 'issued_book_id_' . $book_id, $book_id);

        return new WP_REST_Response(['user_id' => $library_member_id, 'message' => 'Book issued successfully'], 201);
    }

    // Return a book to a library
    public function return_book(WP_REST_Request $request) {
        $library_id         = intval($request['library_id']);
        $book_id            = intval($request['book_id']);
        $library_member_id  = intval($request['library_member_id']);

        // Check if the library exists
        if (!get_post($library_id) || get_post_type($library_id) !== 'library') {
            return new WP_Error('invalid_library', 'Library not found', ['status' => 404]);
        }
        
        // Check if the book exists
        if (!get_post($book_id) || get_post_type($book_id) !== 'book') {
            return new WP_Error('invalid_book', 'Book not found', ['status' => 404]);
        }

        // Check if the library member exists
        if (!get_user_by('ID', $library_member_id)) {
            return new WP_Error('invalid_library_member', 'Library member not found', ['status' => 404]);
        }

        // Check if the book is issued to the library member
        $issued_book = get_user_meta($library_member_id, 'issued_book_id_' . $book_id, true);
        if (!$issued_book) {
            return new WP_Error('book_not_issued', 'Book not issued', ['status' => 400]);
        }

        // Remove the issued book metadata
        delete_user_meta($library_member_id, 'issued_book_id_' . $book_id);

        // Remove the issued library metadata
        delete_user_meta($library_member_id, 'issued_library_id_' . $library_id);

        return new WP_REST_Response(['user_id' => $library_member_id, 'message' => 'Book returned successfully'], 201);
    }

    // Permission checks...
    public function check_permission_admin() {
        return current_user_can('manage_options');
    }

    public function check_permission_logged_in() {
        return is_user_logged_in();
    }
}