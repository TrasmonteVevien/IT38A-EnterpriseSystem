<?php

class Firewall {
    private $blockedIPs = [];
    private $rateLimits = [];
    private $maxRequests = 100; // Maximum requests per minute
    private $blockDuration = 3600; // Block duration in seconds (1 hour)

    public function __construct() {
        $this->loadBlockedIPs();
    }

    private function loadBlockedIPs() {
        // Load blocked IPs from database or file
        // This is a placeholder - implement your storage method
        $this->blockedIPs = [];
    }

    public function checkRequest() {
        $ip = $this->getClientIP();
        
        // Check if IP is blocked
        if ($this->isIPBlocked($ip)) {
            $this->denyAccess("IP is blocked");
            return false;
        }

        // Check rate limiting
        if (!$this->checkRateLimit($ip)) {
            $this->blockIP($ip);
            $this->denyAccess("Rate limit exceeded");
            return false;
        }

        // Check for suspicious patterns
        if ($this->hasSuspiciousPatterns()) {
            $this->blockIP($ip);
            $this->denyAccess("Suspicious activity detected");
            return false;
        }

        return true;
    }

    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function isIPBlocked($ip) {
        return in_array($ip, $this->blockedIPs);
    }

    private function checkRateLimit($ip) {
        $currentTime = time();
        if (!isset($this->rateLimits[$ip])) {
            $this->rateLimits[$ip] = [
                'count' => 1,
                'timestamp' => $currentTime
            ];
            return true;
        }

        $limit = $this->rateLimits[$ip];
        if ($currentTime - $limit['timestamp'] > 60) {
            // Reset counter if more than a minute has passed
            $this->rateLimits[$ip] = [
                'count' => 1,
                'timestamp' => $currentTime
            ];
            return true;
        }

        if ($limit['count'] >= $this->maxRequests) {
            return false;
        }

        $this->rateLimits[$ip]['count']++;
        return true;
    }

    private function hasSuspiciousPatterns() {
        // Check for common attack patterns
        $suspiciousPatterns = [
            '/\.\.\//', // Directory traversal
            '/<script>/i', // XSS attempts
            '/UNION\s+SELECT/i', // SQL injection
            '/eval\s*\(/i', // Code execution
            '/system\s*\(/i' // System commands
        ];

        $requestData = array_merge($_GET, $_POST, $_SERVER);
        foreach ($requestData as $key => $value) {
            if (is_string($value)) {
                foreach ($suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function blockIP($ip) {
        if (!in_array($ip, $this->blockedIPs)) {
            $this->blockedIPs[] = $ip;
            // Save blocked IPs to storage
            // This is a placeholder - implement your storage method
        }
    }

    private function denyAccess($reason) {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Access Denied',
            'reason' => $reason
        ]);
        exit;
    }
}
?>