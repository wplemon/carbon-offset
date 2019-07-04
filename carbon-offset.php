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

require_once __DIR__ . '/inc/admin-page.php';
require_once __DIR__ . '/inc/Data.php';
require_once __DIR__ . '/inc/Log.php';
require_once __DIR__ . '/inc/Cloverly.php';

new \Carbon_Offset\Log();
new \Carbon_Offset\Cloverly();
