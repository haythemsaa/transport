<?php
/**
 * Logger Class - Advanced Logging System
 *
 * Provides structured logging with different levels and automatic rotation
 */

class Logger {
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    private static $instance = null;
    private $logPath;
    private $maxFileSize = 10485760; // 10 MB
    private $maxFiles = 10;

    /**
     * Private constructor for Singleton
     */
    private function __construct() {
        $this->logPath = __DIR__ . '/../logs/';
        $this->ensureLogDirectory();
    }

    /**
     * Get Logger instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }

        // Create .htaccess to protect logs
        $htaccessPath = $this->logPath . '.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "Order deny,allow\nDeny from all");
        }
    }

    /**
     * Log a message
     *
     * @param string $level Log level (emergency, alert, critical, error, warning, notice, info, debug)
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $channel Log channel (app, security, database, api, etc.)
     */
    public function log($level, $message, array $context = [], $channel = 'app') {
        $logFile = $this->getLogFile($channel);

        // Check if rotation is needed
        $this->rotateIfNeeded($logFile);

        // Format the log entry
        $entry = $this->formatLogEntry($level, $message, $context);

        // Write to file
        file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Also log critical errors to error_log
        if (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR])) {
            error_log("[$level] $message");
        }
    }

    /**
     * Emergency: system is unusable
     */
    public function emergency($message, array $context = [], $channel = 'app') {
        $this->log(self::EMERGENCY, $message, $context, $channel);
    }

    /**
     * Alert: action must be taken immediately
     */
    public function alert($message, array $context = [], $channel = 'app') {
        $this->log(self::ALERT, $message, $context, $channel);
    }

    /**
     * Critical: critical conditions
     */
    public function critical($message, array $context = [], $channel = 'app') {
        $this->log(self::CRITICAL, $message, $context, $channel);
    }

    /**
     * Error: error conditions
     */
    public function error($message, array $context = [], $channel = 'app') {
        $this->log(self::ERROR, $message, $context, $channel);
    }

    /**
     * Warning: warning conditions
     */
    public function warning($message, array $context = [], $channel = 'app') {
        $this->log(self::WARNING, $message, $context, $channel);
    }

    /**
     * Notice: normal but significant condition
     */
    public function notice($message, array $context = [], $channel = 'app') {
        $this->log(self::NOTICE, $message, $context, $channel);
    }

    /**
     * Info: informational messages
     */
    public function info($message, array $context = [], $channel = 'app') {
        $this->log(self::INFO, $message, $context, $channel);
    }

    /**
     * Debug: debug-level messages
     */
    public function debug($message, array $context = [], $channel = 'app') {
        // Only log debug in development
        if (APP_ENV === 'development') {
            $this->log(self::DEBUG, $message, $context, $channel);
        }
    }

    /**
     * Security log - for authentication, authorization, suspicious activity
     */
    public function security($level, $message, array $context = []) {
        $this->log($level, $message, $context, 'security');
    }

    /**
     * Database log - for database queries, errors, slow queries
     */
    public function database($level, $message, array $context = []) {
        $this->log($level, $message, $context, 'database');
    }

    /**
     * API log - for API requests, responses, errors
     */
    public function api($level, $message, array $context = []) {
        $this->log($level, $message, $context, 'api');
    }

    /**
     * Get log file path for channel
     */
    private function getLogFile($channel) {
        $date = date('Y-m-d');
        return $this->logPath . "{$channel}-{$date}.log";
    }

    /**
     * Format log entry
     */
    private function formatLogEntry($level, $message, array $context) {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user']['id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Start with basic info
        $entry = sprintf(
            "[%s] [%s] [User:%s] [IP:%s] %s",
            $timestamp,
            strtoupper($level),
            $userId,
            $ip,
            $message
        );

        // Add context if provided
        if (!empty($context)) {
            $entry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        // Add request info for errors
        if (in_array($level, [self::ERROR, self::CRITICAL, self::ALERT, self::EMERGENCY])) {
            $entry .= sprintf(
                ' | Request: %s %s | User-Agent: %s',
                $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                $_SERVER['REQUEST_URI'] ?? 'N/A',
                substr($userAgent, 0, 100)
            );
        }

        return $entry;
    }

    /**
     * Rotate log file if needed
     */
    private function rotateIfNeeded($logFile) {
        if (!file_exists($logFile)) {
            return;
        }

        $fileSize = filesize($logFile);
        if ($fileSize < $this->maxFileSize) {
            return;
        }

        // Rotate the file
        $timestamp = time();
        $rotatedFile = $logFile . '.' . $timestamp;
        rename($logFile, $rotatedFile);

        // Compress old log file
        if (function_exists('gzopen')) {
            $this->compressLogFile($rotatedFile);
        }

        // Clean up old files
        $this->cleanupOldLogs(dirname($logFile), basename($logFile));
    }

    /**
     * Compress log file
     */
    private function compressLogFile($file) {
        $gzFile = $file . '.gz';

        $fp = fopen($file, 'r');
        $gzfp = gzopen($gzFile, 'w9');

        while (!feof($fp)) {
            gzwrite($gzfp, fread($fp, 1024 * 512));
        }

        fclose($fp);
        gzclose($gzfp);

        // Remove original file
        unlink($file);
    }

    /**
     * Clean up old log files
     */
    private function cleanupOldLogs($directory, $pattern) {
        $files = glob($directory . '/' . $pattern . '.*');

        // Sort by modification time
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Keep only max files
        $filesToDelete = array_slice($files, $this->maxFiles);
        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    /**
     * Get recent logs
     *
     * @param string $channel Log channel
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function getRecentLogs($channel = 'app', $lines = 100) {
        $logFile = $this->getLogFile($channel);

        if (!file_exists($logFile)) {
            return [];
        }

        $file = new SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key() + 1;

        $startLine = max(0, $totalLines - $lines);

        $logs = [];
        $file->seek($startLine);

        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $this->parseLogLine($line);
            }
            $file->next();
        }

        return array_reverse($logs);
    }

    /**
     * Parse log line into structured data
     */
    private function parseLogLine($line) {
        // Parse log format: [timestamp] [level] [User:id] [IP:address] message
        if (preg_match('/^\[(.*?)\] \[(.*?)\] \[User:(.*?)\] \[IP:(.*?)\] (.*)$/', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'user_id' => $matches[3],
                'ip' => $matches[4],
                'message' => $matches[5],
                'raw' => $line
            ];
        }

        return [
            'raw' => $line
        ];
    }

    /**
     * Search logs
     *
     * @param string $query Search query
     * @param string $channel Log channel
     * @param int $maxResults Maximum results
     * @return array
     */
    public function search($query, $channel = 'app', $maxResults = 100) {
        $logFile = $this->getLogFile($channel);

        if (!file_exists($logFile)) {
            return [];
        }

        $results = [];
        $file = new SplFileObject($logFile);

        while (!$file->eof() && count($results) < $maxResults) {
            $line = trim($file->current());
            if (!empty($line) && stripos($line, $query) !== false) {
                $results[] = $this->parseLogLine($line);
            }
            $file->next();
        }

        return $results;
    }

    /**
     * Get log statistics
     *
     * @param string $channel Log channel
     * @return array
     */
    public function getStats($channel = 'app') {
        $logFile = $this->getLogFile($channel);

        if (!file_exists($logFile)) {
            return [
                'total_entries' => 0,
                'by_level' => [],
                'file_size' => 0
            ];
        }

        $stats = [
            'total_entries' => 0,
            'by_level' => [
                'emergency' => 0,
                'alert' => 0,
                'critical' => 0,
                'error' => 0,
                'warning' => 0,
                'notice' => 0,
                'info' => 0,
                'debug' => 0
            ],
            'file_size' => filesize($logFile)
        ];

        $file = new SplFileObject($logFile);
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $stats['total_entries']++;

                // Count by level
                foreach ($stats['by_level'] as $level => $count) {
                    if (stripos($line, '[' . strtoupper($level) . ']') !== false) {
                        $stats['by_level'][$level]++;
                        break;
                    }
                }
            }
            $file->next();
        }

        return $stats;
    }

    /**
     * Clear logs for a channel
     *
     * @param string $channel Log channel
     */
    public function clear($channel = 'app') {
        $pattern = $this->logPath . "{$channel}-*.log*";
        $files = glob($pattern);

        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// Helper function for global access
function logger() {
    return Logger::getInstance();
}
