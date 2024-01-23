<?php
/**
 * Package updater utilities
 *
 * @package Package Updater
 */

/**
 * Register a new package handler
 *
 * @param  string       $hostname Hostname for the handler.
 * @param  class-string $handler  Handler instance.
 */
function wppu_register_handler( string $hostname, string $handler ): void {
    add_filter(
        'wp_package_handlers',
        fn( $h ) => array_merge(
            $h,
            array( $hostname => $handler )
        )
    );
}
