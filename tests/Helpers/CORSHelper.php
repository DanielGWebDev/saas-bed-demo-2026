<?php

namespace Tests\Helpers;

use Symfony\Component\Process\Process;

class CORSHelper
{
    private static string $configPath;
    private static string $backupPath;
    private static ?Process $laravelServer = null;

    private static bool $configChanged = false;

    public static function setUp(): void
    {
        self::$configPath = dirname(__DIR__, 2) . '/config/cors.php';
        self::$backupPath = dirname(__DIR__, 2) . '/config/cors.php.bak';
        if (!copy(self::$configPath, self::$backupPath)) {
            throw new \RuntimeException('Failed to copy cors.php to cors.php.bak');
        }
    }

    public static function tearDown(): void
    {
        if (file_exists(self::$backupPath)) {
            copy(self::$backupPath, self::$configPath);
            unlink(self::$backupPath);
        }
        if (self::$configChanged) {
            self::restartLaravelServer();
            self::$configChanged = false;
        }
    }

    public static function setConfig(array $overrides): void
    {
        self::$configChanged = true;
        $config = include self::$configPath;
        $config = array_merge($config, $overrides);

        $content = '<?php' . "\n" . "\n" . 'return [' . "\n";
        foreach ($config as $key => $value) {
            $content .= '    \'' . $key . '\' => ' . self::exportValue($value) . ',' . "\n";
        }
        $content .= '];' . "\n";

        file_put_contents(self::$configPath, $content);
        self::restartLaravelServer();
    }

    public static function restoreConfig(): void
    {
        $backupPath = dirname(__DIR__, 2) . '/config/cors.php.bak';
        $configPath = dirname(__DIR__, 2) . '/config/cors.php';
        if (file_exists($backupPath)) {
            copy($backupPath, $configPath);
            unlink($backupPath);
        }
    }

    public static function startLaravelServer(): void
    {
        self::$laravelServer = new Process(
            ['php', '-d', 'opcache.enable=0', 'artisan', 'serve', '--host=0.0.0.0'],
            dirname(__DIR__, 2),
            ['DB_DATABASE' => dirname(__DIR__, 2) . '/database/database.sqlite', 'DB_CONNECTION' => 'sqlite']
        );
        self::$laravelServer->start();
        sleep(5);

        $attempts = 0;
        while ($attempts < 10) {
            $connection = @fsockopen('myapp.test', 8000, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);
                break;
            }
            sleep(1);
            $attempts++;
        }
    }

    public static function stopLaravelServer(): void
    {
        if (self::$laravelServer) {
            self::$laravelServer->stop();
            self::$laravelServer = null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            exec('netstat -ano | findstr :8000', $output);
            foreach ($output as $line) {
                preg_match('/\s+(\d+)$/', trim($line), $matches);
                if (!empty($matches[1])) {
                    exec("taskkill /F /PID {$matches[1]} 2>nul");
                }
            }
            exec('netstat -ano | findstr :8000', $after);
        }
    }

    private static function restartLaravelServer(): void
    {
        self::stopLaravelServer();
        self::startLaravelServer();
    }

    private static function exportValue(mixed $value): string
    {
        if (is_array($value)) {
            if (empty($value)) {
                return '[]';
            }
            $items = array_map(fn($v) => '\'' . $v . '\'', $value);
            return '[' . implode(', ', $items) . ']';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value)) {
            return (string)$value;
        }
        return '\'' . $value . '\'';
    }
}
