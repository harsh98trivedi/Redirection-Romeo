<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Romeo_Redirect {

    private $option_key = 'redirection_romeo_rules';

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'handle_redirections' ), 1 );
    }

    public function handle_redirections() {
        if ( is_admin() ) {
            return;
        }

        // Normalize path: trim slashes
        $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

        // Fetch all redirects
        // Optimization: In a huge scale, this should be a custom table query. 
        // For standard WP option usage, this is fine.
        $redirects = get_option( $this->option_key, array() );

        if ( empty( $redirects ) || ! is_array( $redirects ) ) {
            return;
        }

        foreach ( $redirects as $key => $r ) {
            // Case insensitive match often desired, but let's stick to exact for speed/strictness unless requested.
            // Using urldecode to match "my-slug" against "my-slug".
            if ( urldecode( $path ) === urldecode( $r['slug'] ) ) {
                
                $target_url = '';

                if ( 'post' === $r['type'] ) {
                    $permalink = get_permalink( $r['target'] );
                    if ( $permalink ) {
                        $target_url = $permalink;
                    } else {
                        return; // Post might be deleted or not found.
                    }
                } else {
                    $target_url = $r['target'];
                }

                // Append query strings if they exist? 
                // Basic redirect usually keeps them or drops them. Let's strict redirect for now.
                // If the user wants to pass parameters, we might need to add that logic.
                // For now, simple redirect.

                // Update Hit Counter
                // Check if index exists to be safe
                if( isset($redirects[$key]) ) {
                    $redirects[$key]['hits'] = isset($r['hits']) ? $r['hits'] + 1 : 1;
                    update_option( $this->option_key, $redirects );
                }

                // Determine Code
                $code = isset( $r['code'] ) ? intval( $r['code'] ) : 301;
                if (!in_array($code, [301, 302, 307, 308])) {
                    $code = 301;
                }

                wp_redirect( $target_url, $code );
                exit;
            }
        }
    }
}
