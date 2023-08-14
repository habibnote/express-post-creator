<?php
// Include WordPress core files
require_once(ABSPATH . 'wp-load.php');

// Define user details
$new_author_username = 'newauthor';
$new_author_password = 'password';
$new_author_email = 'newauthor@example.com';

// Check if the username already exists
if (!username_exists($new_author_username)) {
    // Create a new user
    $new_author_id = wp_create_user($new_author_username, $new_author_password, $new_author_email);

    if (!is_wp_error($new_author_id)) {
        // Assign a role to the new user
        $new_author = new WP_User($new_author_id);
        $new_author->set_role('author'); // Change 'author' to the desired user role

        echo "New author created with ID: $new_author_id";
    } else {
        echo "Error creating new author: " . $new_author_id->get_error_message();
    }
} else {
    echo "Username already exists. Choose a different username.";
}
