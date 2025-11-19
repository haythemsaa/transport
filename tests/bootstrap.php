<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the test environment
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Europe/Paris');

// Define test environment
define('TEST_MODE', true);
define('ROOT_PATH', dirname(__DIR__));

// Load configuration
if (file_exists(ROOT_PATH . '/config/config.php')) {
    require_once ROOT_PATH . '/config/config.php';
}

// Override database config for testing
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'teleroute_test');
    define('DB_USER', getenv('DB_USER') ?: 'teleroute_test');
    define('DB_PASS', getenv('DB_PASS') ?: 'test_password');
    define('DB_CHARSET', 'utf8mb4');
}

// Load helpers
$helpersDir = ROOT_PATH . '/helpers';
if (is_dir($helpersDir)) {
    foreach (glob($helpersDir . '/*.php') as $helperFile) {
        require_once $helperFile;
    }
}

// Load models
$modelsDir = ROOT_PATH . '/models';
if (is_dir($modelsDir)) {
    foreach (glob($modelsDir . '/*.php') as $modelFile) {
        require_once $modelFile;
    }
}

// Autoloader for Composer dependencies (if using Composer)
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Helper function to clean test database
function cleanTestDatabase()
{
    try {
        $db = Database::getInstance()->getConnection();

        // Disable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');

        // Truncate test data tables
        $tables = ['messages', 'notifications', 'saved_searches', 'favorites', 'reviews'];
        foreach ($tables as $table) {
            $db->exec("TRUNCATE TABLE `$table`");
        }

        // Re-enable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    } catch (Exception $e) {
        error_log('Test database cleanup failed: ' . $e->getMessage());
    }
}

// Helper function to create test user
function createTestUser($email = 'test@example.com', $userType = 'both')
{
    try {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            INSERT INTO users (email, password, user_type, company_name, phone, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE email = email
        ");

        $hashedPassword = password_hash('TestPassword123!', PASSWORD_BCRYPT);

        $stmt->execute([
            $email,
            $hashedPassword,
            $userType,
            'Test Company',
            '0123456789'
        ]);

        return $db->lastInsertId();

    } catch (Exception $e) {
        error_log('Test user creation failed: ' . $e->getMessage());
        return false;
    }
}

// Helper function to delete test user
function deleteTestUser($email = 'test@example.com')
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$email]);
    } catch (Exception $e) {
        error_log('Test user deletion failed: ' . $e->getMessage());
    }
}

// Display test environment info
if (php_sapi_name() === 'cli') {
    echo "\n";
    echo "================================================\n";
    echo "  Teleroute Marketplace - Test Suite\n";
    echo "================================================\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Database: " . DB_NAME . "@" . DB_HOST . "\n";
    echo "Timezone: " . date_default_timezone_get() . "\n";
    echo "================================================\n";
    echo "\n";
}
