<?php
// Autoloader to load classes based on namespaces automatically without manual requires
spl_autoload_register(function ($class) {
    // Convert namespace backslashes to directory separators
    $file = __DIR__ . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load the router and resolve the current request
$router = require_once __DIR__ . '/../routes/api.php';
$router->resolve();
