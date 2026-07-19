<?php
// Set execution working directory to project root
chdir(dirname(__DIR__));

// Get request URI
$uri = $_SERVER['REQUEST_URI'];
$uri = explode('?', $uri)[0];
$uri = ltrim($uri, '/');

// Default entrypoint
if ($uri === '' || $uri === 'index.php') {
    require 'index.php';
    exit;
}

// Serve requested PHP file
if (file_exists($uri)) {
    if (pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
        require $uri;
        exit;
    }
}

// Fallback: If requesting a directory, look for index.php inside it
if (is_dir($uri)) {
    $indexFile = rtrim($uri, '/') . '/index.php';
    if (file_exists($indexFile)) {
        require $indexFile;
        exit;
    }
}

// Fallback to 404
http_response_code(404);
echo "404 Not Found";
