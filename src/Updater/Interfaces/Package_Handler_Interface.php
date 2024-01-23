<?php
/**
 * Package_Handler_Interface interface file.
 *
 * @package Package Updater
 * @subpackage Interfaces
 */

namespace Oblak\WP\Updater\Interfaces;

/**
 * Shared interface for package handlers
 */
interface Package_Handler_Interface {
    /**
     * Get the package types and supported slugs for this handler
     *
     * @return string[]
     */
    public function get_types(): array;

    /**
     * Get the remote data
     *
     * @param  string $slug The slug.
     * @return array|false The response or false on failure
     */
    public function get_remote_data( string $slug ): array|false;

    /**
     * Undocumented function
     *
     * @param  bool|array                                                                                 $update_data Update data.
     * @param  array{Name: string, Version: string, PluginURI: string, Author: string, UpdateURI: string} $plugin_data Plugin data.
     * @param  string                                                                                     $plugin_file Plugin file.
     * @param  string[]                                                                                   $locales     Locales.
     * @return array{
     *   id:            string,
     *   slug:          string,
     *   version:       string,
     *   url:           string,
     *   package:       string,
     *   homepage:      string,
     *   download_link: string,
     *   tested:        string,
     *   requires_php:  string,
     *   auto_update:   bool,
     *   icons:         array{1x: string, 2x: string, svg: string},
     *   banners:       array{low: string, high: string}
     *   banners_rtl:   array{low: string, high: string}
     *   last_updated:  string,
     *   sections:      array{
     *       description: string,
     *       installation: string,
     *       changelog: string,
     *       screenshots: string,
     *       faq: string,
     *       reviews: string,
     *   }
     *   contributors:  array<string, array{display_name: string, profile: string, avatar: string}>,
     *   translations:  array{
     *       language:   string,
     *       version:    string,
     *       updated:    string,
     *       package:    string,
     *       autoupdate: string
     *   }[]
     * }|false
     */
    public function check_package_updates(
        bool|array $update_data,
        array $plugin_data,
        string $plugin_file,
        array $locales,
    ): array|false;
}
