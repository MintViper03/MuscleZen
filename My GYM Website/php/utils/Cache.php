<?php
class Cache {
    private static $instance = null;
    private $cache = [];
    private $cache_path;
    
    private function __construct() {
        $this->cache_path = __DIR__ . '/../../cache/';
        if (!file_exists($this->cache_path)) {
            mkdir($this->cache_path, 0777, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }
    
    public function get($key) {
        // Check memory cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        // Check file cache
        $file = $this->cache_path . md5($key) . '.cache';
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $cached = unserialize($data);
            
            if ($cached['expires'] > time()) {
                $this->cache[$key] = $cached['data'];
                return $cached['data'];
            }
            
            // Remove expired cache
            unlink($file);
        }
        
        return null;
    }
    
    public function set($key, $value, $ttl = 3600) {
        // Store in memory
        $this->cache[$key] = $value;
        
        // Store in file
        $cached = [
            'data' => $value,
            'expires' => time() + $ttl
        ];
        
        $file = $this->cache_path . md5($key) . '.cache';
        file_put_contents($file, serialize($cached));
    }
    
    public function delete($key) {
        unset($this->cache[$key]);
        $file = $this->cache_path . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function clear() {
        $this->cache = [];
        array_map('unlink', glob($this->cache_path . '*.cache'));
    }
}
