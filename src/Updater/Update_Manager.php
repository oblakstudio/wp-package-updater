<?php
/**
 * Update_Manager class file
 *
 * @package Package Updater
 */

namespace Oblak\WP\Updater;

/**
 * Package Updater
 */
class Update_Manager {
    /**
     * Class instance
     *
     * @var static
     */
    protected static ?Update_Manager $instance = null;

    /**
     * Array holding hostnames and their handlers
     *
     * @var array<string, Package_Handler_Interface>
     */
    protected $handlers = array();

    /**
     * Class Constructor
     */
    protected function __construct() {
        add_action( 'init', array( $this, 'load_handlers' ), 0 );
        add_action( 'init', array( $this, 'init_handlers' ), 1 );
    }

    /**
     * Runs the singleton
     */
    public static function init(): void {
        static::$instance ??= new static();
    }

    /**
     * Checks if the singleton has been initialized
     *
     * @return bool
     */
    public static function initialized(): bool {
        return null !== static::$instance;
    }

    /**
     * Loads the update handlers
     */
    public function load_handlers(): void {
        $this->handlers = array_map(
            fn( $h ) => ( new $h() ),
            apply_filters( 'wp_package_handlers', $this->handlers ) //phpcs:ignore WooCommerce.Commenting
        );
    }

    /**
     * Performs the initialization of the handlers
     *
     * Each handler hooks into:
     * 1. the `update_{$type}_{$hostname}` filter to check for updates.
     * 2. the `{$type}_api` filter to display the package info.
     */
    public function init_handlers(): void {
        $types = array();

        foreach ( $this->handlers as $hostname => $handler ) {
            foreach ( $handler->get_types() as $type ) {
                $types[ $type ] = $type;

                add_filter( "update_{$type}_{$hostname}", array( $handler, 'check_package_updates' ), 10, 4 );
            }
        }

        foreach ( $types as $type ) {
            add_filter( "{$type}_api", array( $this, 'display_package_info' ), 99, 3 );
        }
    }

    /**
     * Displays the package info
     *
     * @param  object|array|bool $result The result. Package info or false.
     * @param  string            $action The action. Either `plugin_information` or `theme_information`.
     * @param  object            $args   The args.   api arguments.
     * @return object|array|bool         The result. Package info or false.
     */
    public function display_package_info( object|array|bool $result, $action, object $args ): object|array|false {
        if ( ! in_array( $action, array( 'plugin_information', 'theme_information' ), true ) ) {
            return $result;
        }

        $data = $this->handlers[ $this->get_hostname( $args->slug ) ]?->get_remote_data( $args->slug );

        return $data ? (object) $data : $result;
    }

    /**
     * Gets the update uri hostname for a given slug
     *
     * @param  string $slug The slug.
     * @return string|false The handler or false if not found.
     */
    public function get_hostname( $slug ): string|false|null {
        $plugin = array_filter(
            get_plugins(),
            fn( $basename ) => str_starts_with( $basename, $slug . '/' ),
            ARRAY_FILTER_USE_KEY
        );

        if ( empty( $plugin ) ) {
            return null;
        }

        $data = array_shift( $plugin );

        $url = wp_parse_url( $data['UpdateURI'] ?? '', PHP_URL_HOST );

        if ( ! $url ) {
            return null;
        }

        return $url;
    }
}
