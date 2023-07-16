<?php
/**
 * Base Updater
 *
 * @package Melany
 */

namespace Oblak\WP;

use WP_Error;

/**
 * Base Updater class
 *
 * @version 1.0.0
 */
abstract class Base_Updater {

    /**
     * Theme / Plugin slug
     *
     * @var string
     */
    protected $slug;

    /**
     * Class constructor
     *
     * @param string $slug Theme / plugin slug.
     * @param string $type Type of updater. Can be 'theme' or 'plugin'.
     */
    public function __construct( $slug, $type ) {
        $this->slug = $slug;

        $this->init_hooks( $type );
    }

    /**
     * Initialize hooks
     *
     * @param string $type Type of updater. Can be 'theme' or 'plugin'.
     */
    protected function init_hooks( $type ) {
        add_filter( "{$type}_api", array( $this, "display_{$type}_info" ), 99, 3 );
        add_filter( "pre_set_site_transient_update_{$type}", array( $this, "update_{$type}_transient" ), 30 );
    }

    /**
     * Get the update url
     *
     * @return string
     */
    abstract protected function get_update_url();

    /**
     * Get the current version of the plugin / theme
     *
     * @return string
     */
    abstract protected function get_current_version();

    /**
     * Get headers for the update request
     *
     * @return array
     */
    protected function get_headers() {
        return array();
    }

    /**
     * Get the transient prefix
     *
     * @return string
     */
    protected function get_transient_prefix() {
        return 'updater_';
    }

    /**
     * Get the transient name
     *
     * @return string
     */
    final protected function get_transient_name() {
        return $this->get_transient_prefix() . $this->slug . '_data';
    }

    /**
     * Get version data for the current plugin
     *
     * @return array|false The plugin / theme info or false if not found
     */
    final protected function get_version_data() {
        $version_data = wp_get_environment_type() === 'production'
            ? get_site_transient( $this->get_transient_name() )
            : false;

        if ( false !== $version_data ) {
            return $version_data;
        }

        $current_version = $this->get_current_version();
        $repo_version    = $this->get_data_from_repo();

        if ( false === $repo_version ) {
            return false;
        }

        if ( version_compare( $current_version, $repo_version['version'], '>=' ) ) {
            $repo_version['new_version']   = $current_version;
            $repo_version['package']       = '';
            $repo_version['download_link'] = '';
        }

        set_site_transient( $this->get_transient_name(), $repo_version, DAY_IN_SECONDS );

        return $repo_version;
    }

    /**
     * Get the plugin / theme info from the repo
     *
     * @return array|false The plugin / theme info or false if not found
     */
    final protected function get_data_from_repo() {
        $response = $this->send_request();

        if ( ! $this->validate_response( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );

        if ( empty( $body ) ) {
            return false;
        }

        $data = json_decode( $body, true );

        return $data ? $data : false;
    }

    /**
     * Validates the response
     *
     * @param array|WP_Error $response The response.
     * @return bool
     */
    protected function validate_response( $response ) {
        return ! is_wp_error( $response )
            && 200 === wp_remote_retrieve_response_code( $response )
            && ! empty( wp_remote_retrieve_body( $response ) );
    }

    /**
     * Send the update request to the repo
     *
     * @return array|WP_Error The response or WP_Error on failure
     */
    protected function send_request() {
        return wp_remote_get(
            $this->get_update_url(),
            array(
                'timeout' => 10,
                'headers' => $this->get_headers(),
            )
        );
    }
}
