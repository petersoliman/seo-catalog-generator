<?php
// Configuration file for Products API

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'products_api');
define('DB_USER', 'root');
define('DB_PASS', '');

// API configuration
define('API_VERSION', '1.0');
define('API_NAME', 'Products API');
define('MAX_RESULTS', 1000); // Maximum number of results to return

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// CORS settings
define('ALLOWED_ORIGINS', ['*']); // In production, specify actual domains
define('ALLOWED_METHODS', ['GET', 'POST', 'OPTIONS']);

// Response settings
define('DEFAULT_TIMEZONE', 'UTC');
date_default_timezone_set(DEFAULT_TIMEZONE);
?>
