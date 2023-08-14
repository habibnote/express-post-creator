<?php 

/*
 * Plugin Name:       Express Post Creator
 * Plugin URI:        https://me.habibnote.com
 * Description:       Create a new post with ajax and notifiy to the author with his email.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Md. Habibur Rahman
 * Author URI:        https://me.habibnote.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       express-post-creator
*/

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Express_Post_Creator {
    public static $instance = '';

    /**
     * Main class constructor
     */
    private function __construct() {
        add_action( 'wp_enqueue_scripts', [$this, 'ERC_load_assets'] );
        add_action( 'wp_ajax_EPC_ajax_call', [$this, 'ERC_ajax_init'] );
        add_shortcode( 'ep_cretor', [$this, 'EPC_shortcode_init'] );
    }

    /**
     * Intialize Ajax Request
     */
    function ERC_ajax_init() {

        //get this data from js
        $post_title =  $_POST['postTitle'] ?? '';
        $post_content = $_POST['postContent'] ?? '';
        $author_name = $_POST['authorName'] ?? '';
        $author_email = $_POST['authorEmail'] ?? '';

        /**
         * Creating a author
         */
        if( $author_name != '' && $author_email != '' ){
            
            $author_user_name = preg_replace('/\s+/', '', $author_name); //remove white space from name
            $author_user_email = $author_email;
            $author_user_password = '123456';

            if( ! username_exists( $author_user_name ) ){
                // Create a new user
                $new_author_id = wp_create_user( $author_user_name, $author_user_password, $author_user_email );

                if ( ! is_wp_error( $new_author_id ) ) {

                    // Assign a role to the new user
                    $new_author = new WP_User( $new_author_id );
                    $new_author->set_role( 'author' );

                }
            }
        }

        /**
         * Creating a post
         */
        if( $post_title != '' && $post_content != '' ) {
            $author_id = isset( $new_author_id ) ? $new_author_id : 1;

            $EPC_post = array(
                'post_title'    => $post_title,
                'post_content'  => $post_content,
                'post_status'   => 'publish',
                'post_author'   => $author_id,
                'post_type'     => 'post'
            );

            $new_post_id = wp_insert_post( $EPC_post ); //creating post

            //Sent mail 
            if( $new_post_id ) {
                $post_permalink = get_permalink( $new_post_id );

                $user_email = $author_email;
                $subject = "{$post_title} is published now";
                $mail_content = "Your post is now live please see it form here {$post_permalink}";

                // Send the email
                wp_mail( $user_email, $subject, $mail_content );

                $massage = "Post Created Successfully & Chek you inbox for getting Conformation mail";
            }

        }else{
            $massage = "Please Fill up all field";
        }

        echo $massage;
        
        wp_insert_post( $wordpress_post );

        die();
    }

    /**
     * Method for Load all plugin assets
     */
    function ERC_load_assets() {

        wp_enqueue_style( 'style-css', plugin_dir_url( __FILE__ ) . '/assets/css/style.css', null, time() );

        if( ! is_admin() && is_single() ) {
            wp_enqueue_script( 'EPC-scipt-js', plugin_dir_url( __FILE__ ) . "/assets/js/script.js", ['jquery'], '0.0.1', true );

            $ajax_url = admin_url( 'admin-ajax.php' );
            wp_localize_script( 'EPC-scipt-js', 'urls', ['ajaxUrl' => $ajax_url] );
        }
    }

    /**
     * Handle Main shortcode callback
     */
    function EPC_shortcode_init() {

        $html_form = <<<EOD
        <form class='epc-from-wrapper'> 
            <p>
                <input type="text" id="epc-post-title" placeholder="Post Title" />
            </p>
            <p> 
                <textarea id="epc-content" placeholder="Content"></textarea>
            </p>
            <p> 
                <input type="text" id="epc-author-name" placeholder="Your Name" />
            </p>
            <p> 
                <input type="email" id="epc-author-email" placeholder="Your Email" />
            </p>
            <p> 
                <button type="submit" id="epc-submit-button" >Create Post</button>
            </p>
        </form>
        <p id="show-massage"></p>
EOD;    

        return $html_form;
    }

    /**
     * Initialize a singleton instance
     */
    public static function getInit() {

        if( ! self::$instance ){
            self::$instance = new Express_Post_Creator();
        }

        return self::$instance;
    }
}

/**
 * For Plugin Initilization
 */
function express_post_creator() {
    return Express_Post_Creator::getInit();
}

/**
 * Plugin run from here
 */
express_post_creator();
