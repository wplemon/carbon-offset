<?php
/**
 * Plugin Name:   Carbon Offset
 * Description:   Offset your site's carbon footprint.
 * Author:        Ari Stathopoulos (@aristath)
 * Author URI:    https://aristath.github.io
 * Version:       1.0
 * Text Domain:   carbon-offset
 * Requires WP:   5.0
 * Requires PHP:  5.6
 *
 * @package   Carbon Offset
 * @author    Ari Stathopoulos (@aristath)
 * @copyright Copyright (c) 2019, Ari Stathopoulos (@aristath)
 * @license   https://opensource.org/licenses/GPL-2.0
 * @since     1.0
 */

require_once __DIR__ . '/inc/Plugin.php';

/**
 * Init the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function carbon_offset() {
	$carbon_offset = new \CarbonOffset\Plugin();
	$carbon_offset->init();
}
carbon_offset();
