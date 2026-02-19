<?php
/**
 * Lightweight PSR-4 autoloader for Dompdf 3.x without Composer.
 * Place this file in: your-theme/lib/dompdf/autoload.inc.php
 * Required directory layout (siblings of /dompdf):
 *   /lib/php-font-lib/src/
 *   /lib/php-svg-lib/src/
 *   /lib/php-css-parser/src/
 */

if (!defined('ABSPATH')) {
    exit;
}

// --- Basic checks
if (!function_exists('spl_autoload_register')) {
    throw new \RuntimeException('SPL autoload not available on this PHP build.');
}
if (!extension_loaded('mbstring')) {
    // Not strictly fatal, but dompdf strongly relies on mbstring
    @trigger_error('mbstring extension is not enabled. Dompdf may not work correctly.', E_USER_WARNING);
}
if (!extension_loaded('gd')) {
    @trigger_error('gd extension is not enabled. Images/rasterization may fail in Dompdf.', E_USER_WARNING);
}

// --- Helper to normalize path
$__dompdf_base = __DIR__; // .../themes/your-theme/lib/dompdf
$__lib_root    = dirname($__dompdf_base); // .../themes/your-theme/lib

/**
 * Registers a PSR-4 autoloader for a given namespace prefix and base dir.
 */
$__loaders = [];

$__registerPsr4 = static function (string $prefix, string $baseDir) use (&$__loaders) {
    // normalize
    $prefix  = trim($prefix, '\\') . '\\';
    $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    $loader = static function (string $class) use ($prefix, $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        if (is_file($file)) {
            require $file;
        }
    };

    spl_autoload_register($loader, true, true);
    $__loaders[] = $loader;
};

// --- Register Dompdf namespace
$__registerPsr4('Dompdf', $__dompdf_base . DIRECTORY_SEPARATOR . 'src');

// --- Register dependencies (expected as siblings of /dompdf)
$__registerPsr4('FontLib',    $__lib_root . DIRECTORY_SEPARATOR . 'php-font-lib'   . DIRECTORY_SEPARATOR . 'src');
$__registerPsr4('Svg',        $__lib_root . DIRECTORY_SEPARATOR . 'php-svg-lib'    . DIRECTORY_SEPARATOR . 'src');
$__registerPsr4('Sabberworm\\CSS', $__lib_root . DIRECTORY_SEPARATOR . 'php-css-parser' . DIRECTORY_SEPARATOR . 'src');

// Optional: tiny polyfills or globals Dompdf expects (rarely needed in 3.x)

/**
 * Simple sanity check: try to resolve one Dompdf class in dev/admin.
 * Comment out in production if you prefer.
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    if (!class_exists('Dompdf\\Dompdf')) {
        @trigger_error('[dompdf] Autoloader active but Dompdf\\Dompdf not found. Check folder layout for /dompdf/src.', E_USER_WARNING);
    }
}
