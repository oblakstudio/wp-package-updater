<?php // phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
/**
 * Standard_Package_Handler class file
 *
 * @package WordPress Package Updater
 */

namespace Oblak\WP\Updater;

use WP_Error;

/**
 * Standard package handler
 */
abstract class Abstract_Package_Handler implements Interfaces\Package_Handler_Interface {
    /**
     * Get the update url
     *
     * @param string $package_slug The package slug.
     */
    abstract protected function get_update_url( string $package_slug ): string;

    /**
     * {@inheritDoc}
     */
    public function check_package_updates(
        bool|array $update_data,
        array $plugin_data,
        string $plugin_file,
        array $locales,
    ): array|false {
        return $this->get_remote_data( basename( $plugin_file, '.php' ) );
    }

    /**
     * {@inheritDoc}
     */
    public function get_remote_data( string $slug ): array|false {
        $response = $this->send_request( $slug );

        return $this->validate_response( $response )
            ? json_decode( wp_remote_retrieve_body( $response ), true )
            : false;
    }

    /**
     * Send the update request to the repo
     *
     * @param  string $slug   The slug.
     * @return array|WP_Error The response or WP_Error on failure
     */
    protected function send_request( string $slug ): array|WP_Error {
        return wp_remote_get(
            $this->get_update_url( $slug ),
            array(
                'timeout' => 10,
                'headers' => $this->get_headers(),
            )
        );
    }

    /**
     * Get headers for the update request
     *
     * @return array
     */
    protected function get_headers(): array {
        return array();
    }

    /**
     * Validates the response
     *
     * @param array|WP_Error $response The response.
     * @return bool
     */
    protected function validate_response( array|WP_Error $response ): bool {
        return ! is_wp_error( $response )
            && 200 === wp_remote_retrieve_response_code( $response )
            && ! empty( wp_remote_retrieve_body( $response ) );
    }
}
