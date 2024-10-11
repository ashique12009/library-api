<?php
// Book Post Type Class

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class BookPostType {

    // Plugin Constructor
    public function __construct() {
        add_action('init', [$this, 'create_post_type']);
        add_action('add_meta_boxes', [$this, 'add_library_meta_box']);
        add_action('save_post', [$this, 'save_library_meta']);
    }

    // Create Post Type
    public function create_post_type() {
        register_post_type('book', [
            'labels' => [
                'name'          => __('Books'),
                'singular_name' => __('Book')
            ],
            'public'        => true,
            'menu_position' => 5,
            'has_archive'   => true,
            'menu_icon'     => 'dashicons-book',
            'show_in_rest'  => true,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        ]);
    }

    // Add Library Meta Box
    public function add_library_meta_box() {
        add_meta_box(
            'library_meta_box',
            __('Select Library'),
            [$this, 'render_library_meta_box'],
            'book',
            'side',
            'default'
        );
    }

    // Render the library dropdown in the book post editor
    public function render_library_meta_box($post) {
        // Fetch all published libraries
        $libraries = get_posts([
            'post_type'         => 'library',
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
        ]);

        // Retrieve saved library_id for this book
        $selected_library = get_post_meta($post->ID, 'library_id', true);

        // Output the dropdown
        echo '<select name="library_id" id="library_id">';
        echo '<option value="">Select a Library</option>';
        foreach ($libraries as $library) {
            echo '<option value="' . esc_attr($library->ID) . '" ' . selected($selected_library, $library->ID, false) . '>' . esc_html($library->post_title) . '</option>';
        }
        echo '</select>';
    }

    // Save Library Meta
    public function save_library_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!isset($_POST['library_id'])) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, 'library_id', $_POST['library_id']);
    }
}