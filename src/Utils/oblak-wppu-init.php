<?php
/**
 * Package updater initializer
 *
 * @package Package Updater
 */

use Oblak\WP\Updater\Update_Manager;

if ( ! function_exists( 'wp_package_updater_init' ) && function_exists( 'add_action' ) ) {

    /**
     * Initializes the package updater
     */
    function wp_package_updater_init() {
        Update_Manager::init();
    }
    add_action( 'plugins_loaded', 'wp_package_updater_init', 0, 0 );


    if ( did_action( 'plugins_loaded' ) && ! doing_action( 'plugins_loaded' ) && ! Update_Manager::initialized() ) {
        wp_package_updater_init();
    }
}
