<?php
/**
 * Plugin_Updater class file.
 *
 * @package Package Updater
 */

namespace Oblak\WP;

use Exception;
use WP_Upgrader;

/**
 * Base plugin updater
 */
abstract class Plugin_Updater extends Base_Updater {

    /**
     * Plugin basename
     *
     * @var string
     */
    protected $basename;

    /**
     * Class constructor
     *
     * @param  string $slug plugin slug.
     */
    public function __construct( $slug ) {
        parent::__construct( $slug, 'plugins' );

        $this->basename = $this->find_basename( $slug );
    }

    /**
     * Finds the plugin file associated with the slug
     *
     * @param  string $slug Plugin slug.
     * @return string       Plugin file
     *
     * @throws Exception If plugin not found.
     */
    protected function find_basename( $slug ) {
        foreach ( array_keys( get_plugins() ) as $basename ) {
            if ( strpos( $basename, "{$slug}/" ) !== false ) {
                return $basename;
            }
        }

        throw new Exception( esc_html( "Plugin {$slug} not found" ) );
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    final protected function get_current_version() {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return get_plugin_data( WP_PLUGIN_DIR . '/' . $this->basename )['Version'];
    }

    /**
     * Displays our custom plugin info in the plugin details popup
     *
     * @param  object $result Plugin info.
     * @param  string $action Action.
     * @param  object $args   Arguments.
     */
    public function display_plugins_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action || $args->slug !== $this->slug ) {
            return $result;
        }

        return (object) $this->get_package_data();
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    public function transform_response( $response ): object {
        $version_data['plugin'] = $this->basename;
        return (object) $response;
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    protected function get_identifier() {
        return $this->basename;
    }

    /**
     * Hook to clear the update transient when the plugin is updated
     *
     * @param  WP_Upgrader $upgrader_object Upgrader object.
     * @param  array       $options         Hook options.
     */
    public function after_plugins_update( $upgrader_object, $options ) {
        if ( 'update' !== $options['action'] || 'plugin' !== $options['type'] ) {
            return;
        }

        if ( ! in_array( $this->get_identifier(), $options['plugins'] ?? array(), true ) ) {
            return;
        }

        delete_site_transient( $this->get_transient_name() );
    }
}
