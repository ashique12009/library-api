<?php
// Library Post Type Class

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ClassLibraryMember {

    // Constructor - no longer needs the init hook
    public function __construct() {
        // Nothing here now, since role creation will happen on plugin activation
    }

    // Public method to create the role
    public function create_library_member_role() {
        // Add the library-member role with basic reading capabilities
        add_role('library-member', 'Library Member', [
            'read' => true, // Basic read capabilities
        ]);
    }

    // Public method to remove the role (useful on plugin deactivation)
    public function remove_library_member_role() {
        remove_role('library-member');
    }
}