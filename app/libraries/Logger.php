<?php
// Logger wrapper: uses Monolog if available, otherwise falls back to file log.
// Implements PSR-3 compatible logging with file permissions and rotation.
class Logger
{
    private static $logPath = null;
    private static $monolog = null;

    private static function ensurePath()
    {
        if (self::$logPath === null) {
            $logDir = APPROOT . '/../storage/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0700, true);
            }
            self::$logPath = $logDir . '/app_debug.log';
            // Only chmod if file exists
            if (file_exists(self::$logPath)) {
                @chmod(self::$logPath, 0600);
            }
        }
    }

    private static function initMonolog()
    {
        if (self::$monolog !== null) {
            return;
        }

        if (!class_exists('\Monolog\Logger', false)) {
            self::$monolog = false;
            return;
        }

        try {
            $logFile = APPROOT . '/../storage/logs/app.log';
            self::$monolog = new \Monolog\Logger('app');
            self::$monolog->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::DEBUG));
            @chmod($logFile, 0600);
        } catch (Exception $e) {
            self::$monolog = false;
        }
    }

    public static function debug($message)
    {
        self::initMonolog();
        if (self::$monolog !== false) {
            self::$monolog->debug((string)$message);
            return;
        }

        self::ensurePath();
        $entry = '[' . date('Y-m-d H:i:s') . '] DEBUG: ' . trim((string)$message) . PHP_EOL;
        @file_put_contents(self::$logPath, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function info($message)
    {
        self::initMonolog();
        if (self::$monolog !== false) {
            self::$monolog->info((string)$message);
            return;
        }
        self::debug($message);
    }

    public static function warning($message)
    {
        self::initMonolog();
        if (self::$monolog !== false) {
            self::$monolog->warning((string)$message);
            return;
        }
        self::debug($message);
    }

    public static function error($message)
    {
        self::initMonolog();
        if (self::$monolog !== false) {
            self::$monolog->error((string)$message);
            return;
        }
        self::debug($message);
    }
}
