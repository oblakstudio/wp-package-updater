<?php
/**
 * Plugin_Updater class file.
 *
 * @package WooCommerce Sync Service
 * @subpackage Update
 */

namespace Oblak\WP;

use Exception;

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

        $this->maybe_load_files();
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

        throw new Exception( "Plugin {$slug} not found" );
    }

    /**
     * Loads the files with missing functions if needed
     */
    protected function maybe_load_files() {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    final protected function get_current_version() {
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

        return (object) $this->get_version_data();
    }

    /**
     * Check for updates
     *
     * @param  object $transient Plugin update transient.
     * @return object            Modified plugin update transient.
     */
    public function update_plugins_transient( $transient ) {
        $version_data = $this->get_version_data();

        if ( $version_data && '' !== $version_data['package'] ) {
            $version_data['plugin']                  = $this->basename;
            $transient->response[ $this->basename ]  = (object) $version_data;
            $transient->no_update[ $this->basename ] = (object) $version_data;

            return $transient;
        }

        unset( $transient->response[ $this->basename ] );
        unset( $transient->no_update[ $this->basename ] );

        $transient->checked[ $this->basename ] = $version_data['new_version'];

        return $transient;
    }

}
