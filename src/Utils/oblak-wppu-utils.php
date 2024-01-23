<?php
/**
 * Package updater utilities
 *
 * @package Package Updater
 */

use Oblak\WP\Updater\Interfaces\Package_Handler_Interface;

/**
 * Register a new package handler
 *
 * @param  string                    $hostname Hostname for the handler.
 * @param  Package_Handler_Interface $handler  Handler instance.
 */
function wppu_register_handler( string $hostname, Package_Handler_Interface $handler ): void {
    add_filter(
        'wp_package_handlers',
        fn( $h ) => array_merge(
            $h,
            array( $hostname => $handler )
        )
    );
}
