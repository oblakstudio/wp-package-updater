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
        add_filter( "pre_set_site_transient_update_{$type}", array( $this, 'update_transient' ), 30 );
        add_filter( 'upgrader_process_complete', array( $this, "after_{$type}_update" ), 99, 2 );
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
     * Get the identifier for the plugin / theme
     */
    abstract protected function get_identifier();

    /**
     * Get headers for the update request
     *
     * @return array
     */
    protected function get_headers() {
        return array();
    }


    /**
     * Transform the response from the repo
     *
     * @param array $response The response from the repo.
     * @return array|object   The transformed response
     */
    abstract protected function transform_response( $response): array|object;

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
     * Get package data for the current plugin / theme
     *
     * @return array|false The plugin / theme info or false if not found
     */
    final protected function get_package_data() {
        $repo_data = wp_get_environment_type() === 'production'
            ? get_site_transient( $this->get_transient_name() )
            : false;

        if ( false !== $repo_data ) {
            return $repo_data;
        }

        $repo_data = $this->get_data_from_repo();

        if ( false === $repo_data ) {
            return false;
        }

        set_site_transient( $this->get_transient_name(), $repo_data, DAY_IN_SECONDS );

        return $repo_data;
    }

    /**
     * Update the theme / plugin update info transient
     *
     * @param object $transient Transient.
     * @return object           Modified transient
     */
    public function update_transient( $transient ) {
        if ( isset( $transient->response[ $this->get_identifier() ] ) ) {
            return $transient;
        }

        $current_version = $this->get_current_version();
        $repo_data       = $this->get_package_data();

        if ( false === $repo_data ) {
            $transient->checked[ $this->get_identifier() ] = $current_version;
            return $transient;
        }

        // Add the current version to the checked array.
        $transient->checked[ $this->get_identifier() ] = $repo_data['new_version'];

        // If the repository package is newer than the installed version, add to updated array.
        if ( version_compare( $repo_data['new_version'], $current_version, '>' ) ) {
            $transient->response[ $this->get_identifier() ] = $this->transform_response( $repo_data );

            return $transient;
        }

        // If the repository package is the same as the installed version, add to no_update array.
        $transient->no_update[ $this->get_identifier() ] = $this->transform_response( $repo_data );

        return $transient;
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
