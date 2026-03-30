<?php

class ImageCache {
    
    private static $cacheDir = 'cache/images/';
    private static $cacheDuration = 3600; // 1 hour
    
    /**
     * Get cached image or store new one
     */
    public static function get($key, $callback = null) {
        $cacheFile = self::getCachePath($key);
        
        // Check if cache exists and is valid
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < self::$cacheDuration) {
            return file_get_contents($cacheFile);
        }
        
        // Generate new content if callback provided
        if ($callback && is_callable($callback)) {
            $data = $callback();
            self::set($key, $data);
            return $data;
        }
        
        return false;
    }
    
    /**
     * Store image in cache
     */
    public static function set($key, $data) {
        $cacheFile = self::getCachePath($key);
        
        // Create cache directory if not exists
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0755, true);
        }
        
        return file_put_contents($cacheFile, $data) !== false;
    }
    
    /**
     * Remove cached image
     */
    public static function delete($key) {
        $cacheFile = self::getCachePath($key);
        
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        $cacheDir = self::$cacheDir;
        
        if (!is_dir($cacheDir)) {
            return true;
        }
        
        $files = glob($cacheDir . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Get cache file path
     */
    private static function getCachePath($key) {
        $hash = md5($key);
        return self::$cacheDir . substr($hash, 0, 2) . '/' . $hash . '.cache';
    }
    
    /**
     * Generate cache key for QR/barcode
     */
    public static function generateKey($type, $data, $size = null) {
        $keyData = $type . '|' . $data;
        if ($size) {
            $keyData .= '|' . $size;
        }
        return $keyData;
    }
    
    /**
     * Clean expired cache files
     */
    public static function cleanExpired() {
        $cacheDir = self::$cacheDir;
        
        if (!is_dir($cacheDir)) {
            return true;
        }
        
        $files = glob($cacheDir . '*/*');
        $cleaned = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > self::$cacheDuration) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
