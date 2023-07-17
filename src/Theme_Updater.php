<?php
/**
 * Theme_Updater class file.
 *
 * @package WooCommerce Sync Service
 * @subpackage Update
 */

namespace Oblak\WP;

/**
 * Handles theme updates
 */
abstract class Theme_Updater extends Base_Updater {

    /**
     * Class constructor
     *
     * @param  string $slug Theme slug.
     */
    public function __construct( $slug ) {
        parent::__construct( $slug, 'themes' );
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    protected function get_current_version() {
        return wp_get_theme( $this->slug )->get( 'Version' );
    }

    /**
     * Displays our custom theme info in the theme details popup
     *
     * @param  object $result Theme info.
     * @param  string $action Action.
     * @param  object $args   Arguments.
     */
    public function display_themes_info( $result, $action, $args ) {
        if ( 'theme_information' !== $action || $args->slug !== $this->slug ) {
            return $result;
        }

        return (object) $this->get_package_data();
    }

    /**
     * Check for updates
     *
     * @param  object $transient Theme update transient.
     * @return object            Modified theme update transient.
     */
    public function update_themes_transient( $transient ) {
        $version_data = $this->get_package_data();

        if ( $version_data && '' !== $version_data['package'] ) {
            $transient->response[ $this->slug ]  = $version_data;
            $transient->no_update[ $this->slug ] = $version_data;

            return $transient;
        }

        unset( $transient->response[ $this->slug ] );
        unset( $transient->no_update[ $this->slug ] );

        $transient->checked[ $this->slug ] = $version_data['new_version'];

        return $transient;
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    public function transform_response( $response ): array {
        return $response;
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    protected function get_identifier() {
        return $this->slug;
    }

    /**
     * Hook to clear the update transient when the plugin is updated
     *
     * @param  WP_Upgrader $upgrader_object Upgrader object.
     * @param  array       $options         Hook options.
     */
    public function after_themes_update( $upgrader_object, $options ) {
        if ( 'update' !== $options['action'] || 'theme' !== $options['type'] ) {
            return;
        }

        if ( ! in_array( $this->get_identifier(), $options['themes'], true ) ) {
            return;
        }

        delete_site_transient( $this->get_transient_name() );
    }

}
