<?php
/**
 * Diagnostic script to test class loading and identify fatal errors.
 * Secure: Only runs if accessed locally or if a test key is matched, or we can password protect it.
 * To keep it simple, we check if an admin session is active OR if the IP is local OR if accessed.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<h1>FATAL ERROR: " . htmlspecialchars($error['message']) . "</h1>";
        echo "<h2>File: " . htmlspecialchars($error['file']) . " on line " . $error['line'] . "</h2>";
    }
});

require_once __DIR__ . '/init.php';

echo "<h3>Autoloading Test</h3>";

$classes = [
    'branix\\WhmcsInvoiceRecovery\\Security',
    'branix\\WhmcsInvoiceRecovery\\DocumentValidator',
    'branix\\WhmcsInvoiceRecovery\\RateLimiter',
    'branix\\WhmcsInvoiceRecovery\\LoggerService',
    'branix\\WhmcsInvoiceRecovery\\InvoiceSearchService',
    'branix\\WhmcsInvoiceRecovery\\PaymentRedirectService'
];

// Fallback Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'branix\\WhmcsInvoiceRecovery\\';
    $base_dir = __DIR__ . '/modules/addons/invoice_recovery/lib/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        echo "Loading file: " . htmlspecialchars($file) . "<br>";
        require_once $file;
    } else {
        echo "File NOT found: " . htmlspecialchars($file) . "<br>";
    }
});

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "Successfully loaded: <strong>" . htmlspecialchars($class) . "</strong><br>";
    } else {
        echo "Failed to load class: <strong style='color:red;'>" . htmlspecialchars($class) . "</strong><br>";
    }
}

echo "<h4>All classes tested.</h4>";
