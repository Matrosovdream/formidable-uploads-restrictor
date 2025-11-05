<?php
/**
 * Plugin Name: Formidable – Restrict Entry Files
 * Description: Protects all files under /wp-content/uploads/formidable/ and adds a Global Settings section in Formidable to choose allowed user groups (roles).
 * Version: 1.0.0
 * Author: your-name
 * Requires Plugins: formidable
 */

if ( ! defined('ABSPATH') ) { exit; }

define('FRU_BASE_PATH', plugin_dir_path(__FILE__));
define('FRU_BASE_URL',  plugin_dir_url(__FILE__));
define('FRU_VERSION',   '1.0.0');

spl_autoload_register(function ($class) {
    $map = [
        'AdminSettings'               => 'actions/AdminSettings.php',
        'ProtectedFormidableUploads'  => 'classes/ProtectedFormidableUploads.php',
        'FRU\\Options'                => 'classes/helpers/Options.php',
    ];
    if (isset($map[$class])) {
        require_once FRU_BASE_PATH . $map[$class];
        return;
    }
    if (strpos($class, 'FRU\\') === 0) {
        $rel = 'classes/helpers/' . substr($class, 4) . '.php';
        $abs = FRU_BASE_PATH . $rel;
        if (is_file($abs)) {
            require_once $abs;
        }
    }
});

add_action('plugins_loaded', function () {
    \FRU\Options::init();
    if (is_admin()) {
        AdminSettings::boot();
    }
    ProtectedFormidableUploads::init();
});
