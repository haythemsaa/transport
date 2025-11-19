<?php
/**
 * Health Check Endpoint
 *
 * Monitors application and system health
 * Returns JSON with status code 200 (healthy) or 503 (unhealthy)
 *
 * Usage: curl http://localhost/health-check.php
 *
 * For monitoring systems (Uptime Robot, Pingdom, New Relic, etc.)
 */

header('Content-Type: application/json');

// Disable error display for clean JSON output
ini_set('display_errors', 0);
error_reporting(0);

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => []
];

$allHealthy = true;

// =====================================================
// CHECK 1: PHP Version
// =====================================================
$phpVersion = PHP_VERSION;
$phpMinVersion = '7.4';
$phpCheck = version_compare($phpVersion, $phpMinVersion, '>=');

$health['checks']['php'] = [
    'status' => $phpCheck ? 'ok' : 'error',
    'version' => $phpVersion,
    'minimum_required' => $phpMinVersion
];

if (!$phpCheck) {
    $allHealthy = false;
}

// =====================================================
// CHECK 2: Database Connection
// =====================================================
try {
    require_once 'config/database.php';
    $db = Database::getInstance()->getConnection();

    // Test query
    $stmt = $db->query("SELECT 1");
    $stmt->fetch();

    $health['checks']['database'] = [
        'status' => 'ok',
        'connection' => 'established'
    ];
} catch (Exception $e) {
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Connection failed',
        'error' => $e->getMessage()
    ];
    $allHealthy = false;
}

// =====================================================
// CHECK 3: Required PHP Extensions
// =====================================================
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$health['checks']['php_extensions'] = [
    'status' => empty($missingExtensions) ? 'ok' : 'error',
    'required' => $requiredExtensions,
    'missing' => $missingExtensions
];

if (!empty($missingExtensions)) {
    $allHealthy = false;
}

// =====================================================
// CHECK 4: Disk Space
// =====================================================
$diskFree = disk_free_space(__DIR__);
$diskTotal = disk_total_space(__DIR__);
$diskUsedPercent = 100 - (($diskFree / $diskTotal) * 100);
$diskWarningThreshold = 90; // Warn if > 90% used

$health['checks']['disk_space'] = [
    'status' => $diskUsedPercent < $diskWarningThreshold ? 'ok' : 'warning',
    'free' => round($diskFree / 1024 / 1024 / 1024, 2) . ' GB',
    'total' => round($diskTotal / 1024 / 1024 / 1024, 2) . ' GB',
    'used_percent' => round($diskUsedPercent, 2)
];

// Disk space warning doesn't make app unhealthy, just warning
if ($diskUsedPercent >= 95) {
    $allHealthy = false;
}

// =====================================================
// CHECK 5: Required Directories (Writable)
// =====================================================
$requiredDirs = [
    'uploads' => __DIR__ . '/uploads',
    'logs' => __DIR__ . '/logs',
    'cache' => __DIR__ . '/cache'
];

$dirIssues = [];

foreach ($requiredDirs as $name => $path) {
    if (!is_dir($path)) {
        $dirIssues[] = "$name directory does not exist: $path";
    } elseif (!is_writable($path)) {
        $dirIssues[] = "$name directory is not writable: $path";
    }
}

$health['checks']['directories'] = [
    'status' => empty($dirIssues) ? 'ok' : 'error',
    'directories' => array_keys($requiredDirs),
    'issues' => $dirIssues
];

if (!empty($dirIssues)) {
    $allHealthy = false;
}

// =====================================================
// CHECK 6: Configuration Files
// =====================================================
$configFiles = [
    'config/config.php',
    'config/database.php',
    '.htaccess'
];

$missingConfigs = [];

foreach ($configFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missingConfigs[] = $file;
    }
}

$health['checks']['config_files'] = [
    'status' => empty($missingConfigs) ? 'ok' : 'error',
    'required' => $configFiles,
    'missing' => $missingConfigs
];

if (!empty($missingConfigs)) {
    $allHealthy = false;
}

// =====================================================
// CHECK 7: Database Tables
// =====================================================
if (isset($db)) {
    try {
        $requiredTables = [
            'users',
            'freight_offers',
            'vehicle_offers',
            'messages',
            'conversations',
            'transactions',
            'ratings',
            'notifications'
        ];

        $existingTables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existingTables[] = $row[0];
        }

        $missingTables = array_diff($requiredTables, $existingTables);

        $health['checks']['database_tables'] = [
            'status' => empty($missingTables) ? 'ok' : 'error',
            'required' => $requiredTables,
            'existing' => count($existingTables),
            'missing' => array_values($missingTables)
        ];

        if (!empty($missingTables)) {
            $allHealthy = false;
        }
    } catch (Exception $e) {
        $health['checks']['database_tables'] = [
            'status' => 'error',
            'message' => 'Could not check tables',
            'error' => $e->getMessage()
        ];
        $allHealthy = false;
    }
}

// =====================================================
// CHECK 8: Memory Usage
// =====================================================
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');

// Convert memory limit to bytes
$memoryLimitBytes = convertToBytes($memoryLimit);
$memoryUsedPercent = ($memoryUsage / $memoryLimitBytes) * 100;

$health['checks']['memory'] = [
    'status' => $memoryUsedPercent < 90 ? 'ok' : 'warning',
    'used' => round($memoryUsage / 1024 / 1024, 2) . ' MB',
    'limit' => $memoryLimit,
    'used_percent' => round($memoryUsedPercent, 2)
];

// =====================================================
// CHECK 9: Redis (if enabled)
// =====================================================
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->ping();

        $health['checks']['redis'] = [
            'status' => 'ok',
            'connection' => 'established'
        ];
    } catch (Exception $e) {
        $health['checks']['redis'] = [
            'status' => 'optional',
            'message' => 'Redis not available (optional)',
            'error' => $e->getMessage()
        ];
    }
}

// =====================================================
// Overall Health Status
// =====================================================
if (!$allHealthy) {
    $health['status'] = 'unhealthy';
    http_response_code(503); // Service Unavailable
} else {
    http_response_code(200); // OK
}

// Add metadata
$health['app'] = [
    'name' => 'Teleroute Marketplace',
    'version' => '1.0.0',
    'environment' => APP_ENV ?? 'production'
];

// Add uptime if available
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    $health['system'] = [
        'load_average' => [
            '1min' => $load[0],
            '5min' => $load[1],
            '15min' => $load[2]
        ]
    ];
}

// Output JSON
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Helper function
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value) - 1]);
    $value = (int)$value;

    switch ($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
}
