<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Romeo_Admin {

    private $option_key = 'redirection_romeo_rules';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        
        // AJAX
        add_action( 'wp_ajax_rr_save_redirect', array( $this, 'ajax_save_redirect' ) );
        add_action( 'wp_ajax_rr_delete_redirect', array( $this, 'ajax_delete_redirect' ) );
        add_action( 'wp_ajax_rr_search_posts', array( $this, 'ajax_search_posts' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Redirection Romeo', 'redirection-romeo' ),
            __( 'Romeo Redirects', 'redirection-romeo' ),
            'manage_options',
            'redirection-romeo',
            array( $this, 'render_admin_page' ),
            'dashicons-randomize',
            80
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_redirection-romeo' !== $hook ) {
            return;
        }

        $main_file = dirname( __FILE__ ) . '/../redirection-romeo.php';
        wp_enqueue_style( 'romeo-admin-css', plugins_url( 'assets/css/admin.css', $main_file ), array(), '1.1' );
        wp_enqueue_script( 'romeo-admin-js', plugins_url( 'assets/js/admin.js', $main_file ), array(), '1.1', true );

        wp_localize_script( 'romeo-admin-js', 'rr_vars', array(
            'nonce' => wp_create_nonce( 'rr_save_nonce' ),
            'delete_nonce' => wp_create_nonce( 'rr_delete_nonce' )
        ));
    }

    public function render_admin_page() {
        $redirects = get_option( $this->option_key, array() );
        $redirects = array_reverse( $redirects ); // Newest first
        $logo_url = plugins_url( 'assets/images/Redirection-Romeo.svg', dirname( __FILE__ ) . '/../redirection-romeo.php' );
        ?>
        <div class="rr-wrapper">
            
            <div class="rr-header">
                <div class="rr-brand">
                    <div class="rr-logo-icon">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Redirection Romeo Logo">
                    </div>
                    <div>
                        <h1>Redirection Romeo</h1>
                        <small style="color:var(--rr-text-secondary)">By <a href="https://harsh98trivedi.github.io/" target="_blank" style="color:var(--rr-primary); text-decoration:none; font-weight:600;">Harsh Trivedi</a></small>
                    </div>
                </div>
                <div class="rr-header-actions">
                    <button id="rr-btn-new" class="rr-btn rr-btn-primary">
                        <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Create New Redirect', 'redirection-romeo' ); ?>
                    </button>
                </div>
            </div>

            <!-- Creator / Edit Panel -->
            <div id="rr-creator-panel" class="rr-main-card rr-creator-wrapper hidden">
                <div class="rr-creator-header">
                    <h3 id="rr-modal-title"><?php esc_html_e( 'Create Relationship', 'redirection-romeo' ); ?></h3>
                </div>
                
                <form id="rr-form">
                    <div class="rr-form-grid">
                        
                        <!-- Source -->
                        <div class="rr-field">
                            <label><?php esc_html_e( 'Source Slug', 'redirection-romeo' ); ?></label>
                            <div class="rr-input-wrapper">
                                <span class="rr-input-prefix"><?php echo esc_url( home_url( '/' ) ); ?></span>
                                <input class="rr-input" type="text" name="slug" placeholder="my-awesome-link" required>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="rr-field">
                            <label><?php esc_html_e( 'Target Type', 'redirection-romeo' ); ?></label>
                            <select id="rr-target-type" name="type" class="rr-select">
                                <option value="url"><?php esc_html_e( 'External URL', 'redirection-romeo' ); ?></option>
                                <option value="post"><?php esc_html_e( 'Internal Post / Page', 'redirection-romeo' ); ?></option>
                            </select>
                        </div>

                        <!-- Target: URL -->
                        <div class="rr-field" id="rr-group-url">
                            <label><?php esc_html_e( 'Target URL', 'redirection-romeo' ); ?></label>
                            <input class="rr-input" type="url" name="target_url" placeholder="https://google.com">
                        </div>

                        <!-- Target: Post -->
                        <div class="rr-field hidden" id="rr-group-post" style="position:relative;">
                            <label><?php esc_html_e( 'Search Content', 'redirection-romeo' ); ?></label>
                            
                            <input class="rr-input" type="text" id="rr-post-search-input" placeholder="<?php esc_attr_e( 'Type to search pages...', 'redirection-romeo' ); ?>">
                            <input type="hidden" name="target_post_id" id="rr-target-post-id">
                            
                            <div id="rr-search-results" class="rr-autocomplete-results hidden"></div>
                            
                            <div id="rr-selected-post" class="rr-post-selected hidden">
                                <span class="dashicons dashicons-admin-links"></span> 
                                <span class="text"></span>
                                <button type="button" class="rr-btn-icon rr-remove-selection" style="margin-left:auto; height:24px; width:24px;">&times;</button>
                            </div>
                        </div>

                        <!-- HTTP Code -->
                        <div class="rr-field">
                            <label><?php esc_html_e( 'Redirection Code', 'redirection-romeo' ); ?></label>
                            <select name="code" class="rr-select">
                                <option value="301">301 - Permanent</option>
                                <option value="302">302 - Temporary</option>
                                <option value="307">307 - Temporary (No Cache)</option>
                                <option value="308">308 - Permanent (Preserve Method)</option>
                            </select>
                        </div>

                    </div>
                    
                    <div style="display:flex; justify-content:flex-end; gap:12px;">
                        <button type="button" id="rr-cancel" class="rr-btn rr-btn-secondary"><?php esc_html_e( 'Cancel', 'redirection-romeo' ); ?></button>
                        <button type="submit" id="rr-save-btn" class="rr-btn rr-btn-primary"><?php esc_html_e( 'Save Redirect', 'redirection-romeo' ); ?></button>
                    </div>
                </form>
            </div>

            <!-- List View -->
            <div data-view="list" style="margin-top: 24px;">
                
                <div class="rr-toolbar-modern">
                    <div class="rr-search-modern-wrapper">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" id="rr-card-search" class="rr-search-modern" placeholder="<?php esc_attr_e( 'Type to search redirects...', 'redirection-romeo' ); ?>">
                    </div>
                </div>

                <div class="rr-grid-list" id="rr-card-grid">
                    <?php if ( empty( $redirects ) ) : ?>
                        <div class="rr-empty-state">
                            <span class="dashicons dashicons-randomize" style="font-size:64px; height:64px; width:64px; color:#e2e8f0; margin-bottom:24px;"></span>
                            <h3><?php esc_html_e( 'No redirects found', 'redirection-romeo' ); ?></h3>
                            <p><?php esc_html_e( 'Create your first redirect to get started.', 'redirection-romeo' ); ?></p>
                        </div>
                    <?php else : ?>
                        <?php foreach ( $redirects as $r ) : 
                            $data_attr = $r;
                            $target_display = $r['target'];
                            $target_type_badge = 'URL';
                            
                            if( 'post' === $r['type'] ) {
                                $target_type_badge = 'POST';
                                $title = get_the_title( $r['target'] );
                                $data_attr['target_title'] = $title;
                                if( $title ) {
                                    $target_display = $title;
                                } else {
                                    $target_display = '(Deleted Post ID: ' . $r['target'] . ')';
                                }
                            }
                            
                            $full_source = home_url( '/' . $r['slug'] );
                            $json_data = htmlspecialchars(json_encode($data_attr), ENT_QUOTES, 'UTF-8');
                            $status_class = 'status-' . esc_attr($r['code']);
                        ?>
                            <div class="rr-redirect-card <?php echo esc_attr( $status_class ); ?>" id="card-<?php echo esc_attr( $r['id'] ); ?>" data-slug="<?php echo esc_attr( strtolower($r['slug']) ); ?>" data-target="<?php echo esc_attr( strtolower($target_display) ); ?>">
                                
                                <div class="rr-card-header">
                                    <div class="rr-slug-title">
                                        <span>/</span><?php echo esc_html( $r['slug'] ); ?>
                                    </div>
                                    <div class="rr-card-actions">
                                        <a href="<?php echo esc_url( $full_source ); ?>" target="_blank" class="rr-btn-icon" title="Test Link">
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                        <button onclick="rrEdit(<?php echo esc_attr( $json_data ); ?>)" class="rr-btn-icon" title="Edit">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button onclick="rrDelete('<?php echo esc_attr( $r['id'] ); ?>')" class="rr-btn-icon" title="Delete" style="color:var(--rr-danger-text); background:#fff1f2;">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </div>

                                <div class="rr-card-body">
                                    <div class="rr-target-display" title="<?php echo esc_attr( $target_display ); ?>">
                                        <span class="rr-target-label"><?php echo esc_html($target_type_badge); ?> TARGET</span>
                                        <span class="rr-target-text"><?php echo esc_html( $target_display ); ?></span>
                                    </div>
                                </div>

                                <div class="rr-card-footer">
                                    <div class="rr-status-dot">
                                        <?php echo esc_attr( $r['code'] ); ?> Redirect
                                    </div>
                                    <span class="rr-pill-hits">
                                        <?php echo isset($r['hits']) ? esc_html( number_format_i18n( $r['hits'] ) ) : 0; ?> Hits
                                    </span>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_save_redirect() {
        check_ajax_referer( 'rr_save_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $slug = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'url';
        $code = isset( $_POST['code'] ) ? intval( wp_unslash( $_POST['code'] ) ) : 301;
        
        if ( empty( $slug ) ) {
            wp_send_json_error( 'Slug is required.' );
        }

        $target = '';
        if ( 'post' === $type ) {
            $target = isset( $_POST['target_post_id'] ) ? intval( wp_unslash( $_POST['target_post_id'] ) ) : 0;
            if ( ! $target ) {
                wp_send_json_error( 'Please select a post.' );
            }
        } else {
            $target = isset( $_POST['target_url'] ) ? esc_url_raw( wp_unslash( $_POST['target_url'] ) ) : '';
            if ( empty( $target ) ) {
                wp_send_json_error( 'Target URL is required.' );
            }
        }

        $redirects = get_option( $this->option_key, array() );
        
        // Check for duplicates (slug collision), excluding self
        foreach($redirects as $r) {
            if($r['slug'] === $slug && $r['id'] !== $id) {
                wp_send_json_error('Slug is already in use.');
            }
        }

        if ( $id ) {
            // Update
            $updated = false;
            foreach ( $redirects as &$r ) {
                if ( $r['id'] === $id ) {
                    $r['slug']   = $slug;
                    $r['type']   = $type;
                    $r['target'] = $target;
                    $r['code']   = $code;
                    $updated = true;
                    break;
                }
            }
            if ( ! $updated ) {
                // ID provided but not found? Treat as new or error? 
                // Let's safe fallback to new
                $id = uniqid(); 
                $redirects[] = array(
                    'id'     => $id,
                    'slug'   => $slug,
                    'type'   => $type,
                    'target' => $target,
                    'code'   => $code,
                    'hits'   => 0
                );
            }
        } else {
            // Create New
            $id = uniqid();
            $redirects[] = array(
                'id'     => $id,
                'slug'   => $slug,
                'type'   => $type,
                'target' => $target,
                'code'   => $code,
                'hits'   => 0
            );
        }

        update_option( $this->option_key, $redirects );
        wp_send_json_success();
    }

    public function ajax_delete_redirect() {
        check_ajax_referer( 'rr_delete_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $redirects = get_option( $this->option_key, array() );

        foreach ( $redirects as $key => $r ) {
            if ( $r['id'] === $id ) {
                unset( $redirects[ $key ] );
                update_option( $this->option_key, array_values( $redirects ) );
                wp_send_json_success();
            }
        }
        wp_send_json_error( 'Not found' );
    }

    public function ajax_search_posts() {
        // Verify Nonce
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'rr_save_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
        
        $query = new WP_Query( array(
            's' => $term,
            'post_type' => array( 'post', 'page' ),
            'posts_per_page' => 10,
            'post_status' => 'publish'
        ) );

        $results = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'type' => get_post_type()
                );
            }
        }
        wp_reset_postdata();
        wp_send_json_success( $results );
    }
}
