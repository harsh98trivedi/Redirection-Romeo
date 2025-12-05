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

        // 1. Get current path
        // Use wp_unslash before processing
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        
        // Parse URL safely
        $parsed_url = wp_parse_url( $request_uri );
        if ( ! $parsed_url || ! isset( $parsed_url['path'] ) ) {
            return;
        }

        $path = untrailingslashit( $parsed_url['path'] );
        $path = trim( $path, '/' );

        // 2. Load redirects
        $redirects = get_option( $this->option_key, array() );

        if ( empty( $redirects ) ) {
            return;
        }

        // 3. Match
        foreach ( $redirects as $key => &$r ) {
            // Case-insensitive match for slug
            if ( strtolower( $r['slug'] ) === strtolower( $path ) ) {
                
                // Track Hit
                if ( ! isset( $r['hits'] ) ) {
                    $r['hits'] = 0;
                }
                $r['hits']++;
                $redirects[$key] = $r; // Ensure array is updated
                update_option( $this->option_key, $redirects ); // Save hits

                // Resolve Target
                $target_url = '';
                if ( 'post' === $r['type'] ) {
                    $target_url = get_permalink( intval( $r['target'] ) );
                } else {
                    $target_url = $r['target'];
                }

                if ( $target_url ) {
                    // Redirect
                    // Use wp_redirect as we support external domains.
                    // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
                    wp_redirect( $target_url, intval( $r['code'] ) );
                    exit;
                }
            }
        }
    }
}
