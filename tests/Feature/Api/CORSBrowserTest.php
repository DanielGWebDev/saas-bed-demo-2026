<?php

use App\Models\User;
use Symfony\Component\Process\Process;
use Tests\Helpers\CORSHelper;

const CORS_API_ENDPOINT = 'http://myapp.test:8000/api/v1/users';
const CORS_ORIGIN = 'http://myapp.test:3000';
const CORS_ORIGIN_SUBDOMAIN = 'http://sub.myapp.test:3000';
const CORS_ORIGIN_DISALLOWED = 'http://evil.test:3000';
const CORS_TEST_PAGE = CORS_ORIGIN . '/?url=' . CORS_API_ENDPOINT;
const CORS_TEST_PAGE_SUBDOMAIN = CORS_ORIGIN_SUBDOMAIN . '/?url=' . CORS_API_ENDPOINT;
const CORS_TEST_PAGE_DISALLOWED_ORIGIN = CORS_ORIGIN_DISALLOWED . '/?url=' . CORS_API_ENDPOINT;

function corsUserPage(string $origin = CORS_ORIGIN): string
{
    return $origin . '/?url=' . CORS_API_ENDPOINT . '/' . $GLOBALS['testUserId'];
}

beforeAll(function () {
    CORSHelper::restoreConfig();

    if (PHP_OS_FAMILY === 'Windows') {
        exec('netstat -ano | findstr :3000', $output);
        foreach ($output as $line) {
            preg_match('/\s+(\d+)$/', trim($line), $matches);
            if (!empty($matches[1])) {
                exec("taskkill /F /PID {$matches[1]} 2>nul");
            }
        }
    }

    CORSHelper::startLaravelServer();

    $dbPath = dirname(__DIR__, 3) . '/database/database.sqlite';

    if (!file_exists($dbPath)) {
        throw new \RuntimeException('Database file not found. Please run: php artisan migrate --seed');
    }

    try {
        $db = new \PDO('sqlite:' . $dbPath);
        $result = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
        if ($result === false) {
            throw new \RuntimeException('Users table not found. Please run: php artisan migrate --seed');
        }
        $user = $result->fetch();
        if (!$user) {
            throw new \RuntimeException('No users found in database. Please run: php artisan db:seed');
        }
        $GLOBALS['testUserId'] = $user['id'];
    } catch (\RuntimeException $e) {
        throw $e;
    } catch (\Exception $e) {
        throw new \RuntimeException('Database error: ' . $e->getMessage());
    }

    $GLOBALS['corsServer'] = new Process(['node', 'cors-test-server.js'], dirname(__DIR__, 3));
    $GLOBALS['corsServer']->start();

    $attempts = 0;
    while ($attempts < 10) {
        try {
            $connection = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);
                break;
            }
        } catch (\Exception $e) {
            // keep trying
        }
        sleep(1);
        $attempts++;
    }
});

afterAll(function () {
    $GLOBALS['corsServer']->stop();
    CORSHelper::stopLaravelServer();
    CORSHelper::restoreConfig();
});

beforeEach(function () {
    CORSHelper::setUp();
    file_put_contents(storage_path('logs/request_log.txt'), '');
});

afterEach(function () {
    CORSHelper::tearDown();
});

/**
 * CORS Browser Tests - Manual Testing URLs
 *
 * Before testing manually:
 * 1. Start Laravel server: php artisan serve
 * 2. Start Node server: node cors-test-server.js
 * 3. Set cors.php to the appropriate config for each test
 * 4. Replace {id} with an existing user ID from your database
 *
 * Simple GET request
 * - permits from wildcard:           http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET
 * - permits from allowed origin:     http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET
 * - permits from allowed subdomain:  http://sub.myapp.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET
 * - blocks disallowed origin:        http://evil.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET
 * - blocks disallowed subdomain:     http://sub.myapp.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET
 *
 * Preflight OPTIONS request
 * - permits from wildcard:           http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - permits from allowed origin:     http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - permits from allowed subdomain:  http://sub.myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - permits allowed method:          http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - permits allowed header:          http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - blocks disallowed origin:        http://evil.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - blocks disallowed subdomain:     http://sub.myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - blocks disallowed method:        http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22Content-Type%22%3A%22application%2Fjson%22%7D
 * - blocks disallowed header:        http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users/{id}&method=PATCH&headers=%7B%22X-Custom-Header%22%3A%22value%22%7D
 *
 * Credentials
 * - blocks when credentials false:   http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET&credentials=include
 * - permits when credentials true:   http://myapp.test:3000/?url=http://myapp.test:8000/api/v1/users&method=GET&credentials=include
 */

describe('CORS Browser', function () {

    describe('Simple GET request', function () {

        it('permits simple GET request from any origin when wildcard is configured', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $page = visit(CORS_TEST_PAGE . '&method=GET');
            $page->assertSee('SUCCESS: 200');
        });

        it('permits simple GET request from an allowed origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $page = visit(CORS_TEST_PAGE . '&method=GET');
            $page->assertSee('SUCCESS: 200');
        });

        it('permits simple GET request from an allowed subdomain origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_origins_patterns' => ['/^http:\/\/.*\.myapp\.test(:\d+)?$/'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $page = visit(CORS_TEST_PAGE_SUBDOMAIN . '&method=GET');
            $page->assertSee('SUCCESS: 200');
        });

        it('does not permit simple GET request from a disallowed origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_origins_patterns' => [],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $page = visit(CORS_TEST_PAGE_DISALLOWED_ORIGIN . '&method=GET');
            $page->assertSee('ERROR: Failed to fetch');
        });

        it('does not permit simple GET request from a disallowed subdomain origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_origins_patterns' => [],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $page = visit(CORS_TEST_PAGE_SUBDOMAIN . '&method=GET');
            $page->assertSee('ERROR: Failed to fetch');
        });

    });

    describe('Preflight OPTIONS request', function () {

        it('permits preflight request from any origin when wildcard is configured', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);
            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage() . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('SUCCESS: 200');
            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId'] . ' 200');
        });

        it('permits preflight request from an allowed origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage() . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('SUCCESS: 200');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId'] . ' 200');
        });

        it('permits preflight request from an allowed subdomain origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_origins_patterns' => ['/^http:\/\/.*\.myapp\.test(:\d+)?$/'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage(CORS_ORIGIN_SUBDOMAIN) . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('SUCCESS: 200');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId'] . ' 200');
        });

        it('permits preflight request with an allowed method', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['GET', 'HEAD', 'POST', 'PATCH'],
                'allowed_headers' => ['*'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage() . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('SUCCESS: 200');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId'] . ' 200');
        });

        it('permits preflight request with an allowed header', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['content-type', 'authorization'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage() . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('SUCCESS: 200');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId'] . ' 200');
        });

        it('does not permit preflight request with a disallowed origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage(CORS_ORIGIN_DISALLOWED) . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('ERROR: Failed to fetch');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->not->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId']);
        });

        it('does not permit preflight request with a disallowed subdomain origin', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_origins_patterns' => [],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage(CORS_ORIGIN_SUBDOMAIN) . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('ERROR: Failed to fetch');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->not->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId']);
        });

        it('does not permit preflight request with a disallowed method', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['GET', 'HEAD'],
                'allowed_headers' => ['*'],
            ]);

            $headers = json_encode(['Content-Type' => 'application/json']);
            $page = visit(corsUserPage() . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('ERROR: Failed to fetch');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->not->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId']);
        });

        it('does not permit preflight request with a disallowed header', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['content-type'],
            ]);

            $headers = json_encode(['X-Custom-Header' => 'value']);
            $page = visit(corsUserPage() . '&method=PATCH&headers=' . urlencode($headers));
            $page->assertSee('ERROR: Failed to fetch');

            $log = file_get_contents(storage_path('logs/request_log.txt'));
            expect($log)
                ->toContain('OPTIONS api/v1/users/' . $GLOBALS['testUserId'] . ' 204')
                ->not->toContain('PATCH api/v1/users/' . $GLOBALS['testUserId']);
        });

    });

    describe('Credentials', function () {

        it('does not include credentials header when supports_credentials is false', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
                'supports_credentials' => false,
            ]);

            $page = visit(CORS_TEST_PAGE . '&method=GET&credentials=include');
            $page->assertSee('ERROR: Failed to fetch');
        });

        it('includes credentials header when supports_credentials is true', function () {
            CORSHelper::setConfig([
                'allowed_origins' => ['http://myapp.test:3000'],
                'allowed_methods' => ['*'],
                'allowed_headers' => ['*'],
                'supports_credentials' => true,
            ]);

            $page = visit(CORS_TEST_PAGE . '&method=GET&credentials=include');
            $page->assertSee('SUCCESS: 200');
        });
    });
});
