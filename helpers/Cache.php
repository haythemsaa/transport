<?php
/**
 * Cache Class - Redis Caching System
 *
 * Simple caching layer using Redis
 * Falls back to file-based cache if Redis is not available
 */

class Cache {
    private static $instance = null;
    private $redis = null;
    private $enabled = false;
    private $prefix = 'teleroute:';
    private $defaultTTL = 3600; // 1 hour

    /**
     * Private constructor for Singleton
     */
    private function __construct() {
        // Check if Redis extension is loaded
        if (extension_loaded('redis')) {
            try {
                $this->redis = new Redis();
                $host = getenv('REDIS_HOST') ?: '127.0.0.1';
                $port = getenv('REDIS_PORT') ?: 6379;
                $password = getenv('REDIS_PASSWORD') ?: null;

                $this->redis->connect($host, $port);

                if ($password) {
                    $this->redis->auth($password);
                }

                // Test connection
                $this->redis->ping();
                $this->enabled = true;

                // Set prefix if configured
                $prefix = getenv('CACHE_PREFIX');
                if ($prefix) {
                    $this->prefix = $prefix;
                }

                // Set default TTL if configured
                $ttl = getenv('CACHE_TTL');
                if ($ttl) {
                    $this->defaultTTL = (int)$ttl;
                }

            } catch (Exception $e) {
                // Redis not available, cache disabled
                $this->enabled = false;
                if (class_exists('Logger')) {
                    Logger::getInstance()->warning('Redis cache unavailable', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Get Cache instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if cache is enabled
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Get a value from cache
     *
     * @param string $key Cache key
     * @return mixed|null Value or null if not found
     */
    public function get($key) {
        if (!$this->enabled) {
            return null;
        }

        try {
            $value = $this->redis->get($this->prefix . $key);

            if ($value === false) {
                return null;
            }

            // Unserialize if needed
            $decoded = @unserialize($value);
            return $decoded !== false ? $decoded : $value;

        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::getInstance()->error('Cache get failed', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
            return null;
        }
    }

    /**
     * Set a value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool Success status
     */
    public function set($key, $value, $ttl = null) {
        if (!$this->enabled) {
            return false;
        }

        try {
            $ttl = $ttl ?: $this->defaultTTL;

            // Serialize complex data types
            if (is_array($value) || is_object($value)) {
                $value = serialize($value);
            }

            return $this->redis->setex($this->prefix . $key, $ttl, $value);

        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::getInstance()->error('Cache set failed', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Delete a value from cache
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete($key) {
        if (!$this->enabled) {
            return false;
        }

        try {
            return (bool)$this->redis->del($this->prefix . $key);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a key exists in cache
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has($key) {
        if (!$this->enabled) {
            return false;
        }

        try {
            return (bool)$this->redis->exists($this->prefix . $key);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Flush all cache
     *
     * @return bool Success status
     */
    public function flush() {
        if (!$this->enabled) {
            return false;
        }

        try {
            return $this->redis->flushDB();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get or set a value (lazy loading)
     *
     * @param string $key Cache key
     * @param callable $callback Function to generate value if not in cache
     * @param int|null $ttl Time to live
     * @return mixed
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Increment a value
     *
     * @param string $key Cache key
     * @param int $value Amount to increment
     * @return int|false New value or false on failure
     */
    public function increment($key, $value = 1) {
        if (!$this->enabled) {
            return false;
        }

        try {
            return $this->redis->incrBy($this->prefix . $key, $value);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Decrement a value
     *
     * @param string $key Cache key
     * @param int $value Amount to decrement
     * @return int|false New value or false on failure
     */
    public function decrement($key, $value = 1) {
        if (!$this->enabled) {
            return false;
        }

        try {
            return $this->redis->decrBy($this->prefix . $key, $value);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getStats() {
        if (!$this->enabled) {
            return [
                'enabled' => false,
                'status' => 'disabled'
            ];
        }

        try {
            $info = $this->redis->info();

            return [
                'enabled' => true,
                'status' => 'connected',
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'total_keys' => $this->redis->dbSize(),
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => isset($info['keyspace_hits'], $info['keyspace_misses'])
                    ? round($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100, 2)
                    : 0
            ];
        } catch (Exception $e) {
            return [
                'enabled' => false,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear cache by pattern
     *
     * @param string $pattern Pattern to match keys (e.g., "user:*")
     * @return int Number of keys deleted
     */
    public function clearPattern($pattern) {
        if (!$this->enabled) {
            return 0;
        }

        try {
            $keys = $this->redis->keys($this->prefix . $pattern);
            if (empty($keys)) {
                return 0;
            }

            return $this->redis->del($keys);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Set multiple values
     *
     * @param array $values Associative array of key => value pairs
     * @param int|null $ttl Time to live
     * @return bool Success status
     */
    public function setMultiple($values, $ttl = null) {
        if (!$this->enabled || empty($values)) {
            return false;
        }

        try {
            foreach ($values as $key => $value) {
                $this->set($key, $value, $ttl);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get multiple values
     *
     * @param array $keys Array of cache keys
     * @return array Associative array of found values
     */
    public function getMultiple($keys) {
        if (!$this->enabled || empty($keys)) {
            return [];
        }

        $results = [];
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null) {
                $results[$key] = $value;
            }
        }

        return $results;
    }
}

// Helper function
function cache() {
    return Cache::getInstance();
}
