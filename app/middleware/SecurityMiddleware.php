<?php

class SecurityMiddleware {
    private $config;
    private $rateLimits = [];
    private $rateLimitFile;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/security.php';
        $this->rateLimitFile = sys_get_temp_dir() . '/qr_code_rate_limits.json';
        $this->loadRateLimits();
    }

    public function applySecurityHeaders() {
        // Set standard security headers
        foreach ($this->config['headers'] as $header => $value) {
            if ($header === 'content-security-policy') {
                $this->applyCspHeader();
            } else {
                header(ucwords($header, '-') . ': ' . $value);
            }
        }
    }
    
    private function applyCspHeader() {
        if (empty($this->config['csp'])) {
            return;
        }
        
        $csp = [];
        foreach ($this->config['csp'] as $directive => $sources) {
            if (!empty($sources)) {
                $csp[] = $directive . ' ' . implode(' ', (array)$sources);
            }
        }
        
        if (!empty($csp)) {
            header('Content-Security-Policy: ' . implode('; ', $csp));
        }
    }

    public function regenerateSessionId() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function checkRateLimit($identifier) {
        if (!isset($this->config['rate_limiting']['enabled']) || 
            !$this->config['rate_limiting']['enabled'] || 
            (isset($this->config['rate_limiting']['ip_whitelist']) && in_array($identifier, $this->config['rate_limiting']['ip_whitelist']))) {
            return true;
        }
        
        $now = time();
        $window = floor($now / 60); // 1 minute windows
        $maxRequests = $this->config['rate_limiting']['requests_per_minute'];
        
        // Initialize if not exists
        if (!isset($this->rateLimits[$identifier])) {
            $this->rateLimits[$identifier] = [];
        }
        
        // Remove old entries
        $this->rateLimits[$identifier] = array_filter(
            $this->rateLimits[$identifier],
            function($timestamp) use ($window) {
                return $timestamp >= ($window - 1) * 60;
            }
        );
        
        // Check if rate limit exceeded
        if (count($this->rateLimits[$identifier]) >= $maxRequests) {
            $this->saveRateLimits();
            http_response_code(429);
            header('Retry-After: 60');
            if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Too many requests. Please try again later.',
                    'retry_after' => 60
                ]);
            } else {
                echo 'Too many requests. Please try again later.';
            }
            exit;
        }
        
        // Add current request
        $this->rateLimits[$identifier][] = $now;
        $this->saveRateLimits();
        
        // Add rate limit headers
        $remaining = $maxRequests - count($this->rateLimits[$identifier]);
        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: ' . max(0, $remaining));
        header('X-RateLimit-Reset: ' . (($window + 1) * 60));
    }
    
    private function loadRateLimits() {
        if (file_exists($this->rateLimitFile)) {
            $data = file_get_contents($this->rateLimitFile);
            if ($data !== false) {
                $this->rateLimits = json_decode($data, true) ?: [];
            }
        }
    }
    
    private function saveRateLimits() {
        // Clean up old entries first (keep entries from the last 2 windows)
        $now = time();
        $window = floor($now / 60); // 1 minute windows
        $cutoff = ($window - 1) * 60;
        
        foreach ($this->rateLimits as $key => $timestamps) {
            $this->rateLimits[$key] = array_filter($timestamps, function($timestamp) use ($cutoff) {
                return $timestamp >= $cutoff;
            });
            
            // Remove empty entries
            if (empty($this->rateLimits[$key])) {
                unset($this->rateLimits[$key]);
            }
        }
        
        // Save to file
        file_put_contents($this->rateLimitFile, json_encode($this->rateLimits), LOCK_EX);
    }
}
