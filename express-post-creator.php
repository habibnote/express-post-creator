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
        add_action( 'wp_enqueue_scripts', [$this, 'EPC_load_assets'] );
        add_action( 'wp_ajax_EPC_ajax_call', [$this, 'EPC_ajax_init'] );
        add_shortcode( 'ep_cretor', [$this, 'EPC_shortcode_init'] );
    }

    /**
     * Intialize Ajax Request
     */
    function EPC_ajax_init() {

        //Check Nonce Security
        if( check_ajax_referer( 'ajax_nonce', 'nonceS' ) ) :

            //get this data from js
            $post_title = sanitize_text_field( $_POST['postTitle'] ) ?? '';
            $post_content = sanitize_textarea_field( $_POST['postContent'] ) ?? '';
            $author_name = sanitize_title( $_POST['authorName'] ) ?? '';
            $author_email = is_email( sanitize_email( $_POST['authorEmail'] ) ) ?? '';
            
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
                }else{
                    _e( 'Author user alredy exit', 'express-post-creator' );
                }
            }else{
                _e( 'To create author you have to entry valid name and email field', 'express-post-creator' );
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

                $new_post_id = wp_insert_post( $EPC_post ); //creating new post and get post id 

                //Sent mail 
                if( $new_post_id ) {
                    $post_permalink = get_permalink( $new_post_id );

                    $user_email = $author_email;
                    $subject = "{$post_title} is published now";
                    $mail_content = "Your post is now live please see it form here {$post_permalink}";

                    // Send the email
                    wp_mail( $user_email, $subject, $mail_content );

                    $massage = __( 'Post Created Successfully Please check your inbox for getting Conformation mail and post link', 'express-post-creator' );
                }

            }else{
                $massage = __( 'Please Fill up all field', 'express-post-creator' );
            }

            echo $massage;
            die();

        endif;
    }

    /**
     * Method for Load all plugin assets
     */
    function EPC_load_assets() {

        

        if( ! is_admin() && is_single() ) {
            //load style
            wp_enqueue_style( 'EPC-style-css', plugin_dir_url( __FILE__ ) . '/assets/css/style.css', null, time() );

            //load script
            wp_enqueue_script( 'EPC-scipt-js', plugin_dir_url( __FILE__ ) . "/assets/js/script.js", ['jquery'], time(), true );

            //send wp ajax url
            $ajax_url = admin_url( 'admin-ajax.php' );
            wp_localize_script( 'EPC-scipt-js', 'urls', ['ajaxUrl' => $ajax_url] );
        }
    }

    /**
     * Handle Main shortcode callback
     */
    function EPC_shortcode_init() {
        //creating nonce field
        $nonce_field = sprintf( '%s', wp_nonce_field( 'ajax_nonce' ) );

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
                {$nonce_field}
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
