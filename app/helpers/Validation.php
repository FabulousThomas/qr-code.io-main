<?php

class Validation {
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone($phone) {
        return preg_match('/^[0-9+\-\s()]{10,20}$/', $phone);
    }

    public static function validateHexColor($color) {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }

    public static function validateQrType($type) {
        $validTypes = ['url', 'text', 'sms', 'wifi', 'call', 'vcard', 'email', 'whatsapp'];
        return in_array(strtolower($type), $validTypes);
    }

    public static function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'], $maxSize = 1048576) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!in_array($file['type'], $allowedTypes, true)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        return in_array($mime, $allowedTypes, true);
    }
}
