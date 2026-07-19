<?php
// Database Connection Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$db_user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$db_pass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$db_name = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?: 'hotel_mngt_db';

try {
    // Attempt connection directly to the target database
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // If database does not exist (Error code 1049), auto-create database and import schema
    if ($e->getCode() == 1049 || strpos($e->getMessage(), "Unknown database") !== false) {
        try {
            $pdo_root = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass);
            $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            
            $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Read and execute schema.sql
            $schema_file = __DIR__ . '/../database/schema.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                $pdo->exec($sql);
            }
        } catch (PDOException $ex) {
            die("<div style='font-family:sans-serif; padding:20px; background:#fce8e6; color:#c5221f; border-radius:8px;'>
                <h3>Database Connection Error</h3>
                <p>Could not connect or initialize MySQL Database: " . htmlspecialchars($ex->getMessage()) . "</p>
                <p>Please make sure XAMPP MySQL server is running.</p>
            </div>");
        }
    } else {
        // Other error (e.g. invalid password or server down)
        die("<div style='font-family:sans-serif; padding:20px; background:#fce8e6; color:#c5221f; border-radius:8px;'>
            <h3>Database Connection Failed</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p>Please check your MySQL service in XAMPP.</p>
        </div>");
    }
}

// Global sanitization helper function
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Flash message helpers
function set_flash_message($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        
        $bs_class = ($type === 'error') ? 'danger' : $type;
        return "<div class='alert alert-{$bs_class} alert-dismissible fade show' role='alert'>
                    " . htmlspecialchars($message) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return '';
}
