<?php
/**
 * Plugin Name: Redirection Romeo
 * Description: A modern, lightweight redirect manager. Redirect slugs to external URLs or internal posts with style.
 * Version:     1.0.0
 * Author:      Harsh Trivedi
 * Author URI:  https://harsh98trivedi.github.io/
 * Text Domain: redirection-romeo
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Autoloader - simplified manual require for now strictly for this task scope
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-redirect.php';

if ( ! class_exists( 'Redirection_Romeo' ) ) {

	/**
	 * Main Plugin Class
	 */
	class Redirection_Romeo {

		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			// Initialize Admin
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				new Romeo_Admin();
			}

			// Initialize Frontend Redirects
			new Romeo_Redirect();
		}
	}

	// Initialize the plugin.
	Redirection_Romeo::get_instance();
}