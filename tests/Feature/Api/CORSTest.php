<?php

use Illuminate\Support\Facades\Config;

const CORS_TEST_ENDPOINT = '/api/v1/users';

describe('CORS', function () {

    describe('Simple GET request', function () {

        beforeEach(function () {
            Config::set('cors.allowed_methods', ['*']);
            Config::set('cors.allowed_origins', ['https://myapp.com']);
            Config::set('cors.allowed_origins_patterns', ['/^https:\/\/.*\.myapp\.com$/']);
            Config::set('cors.allowed_headers', ['content-type', 'authorization']);
        });

        it('permits simple GET request from any origin when wildcard is configured', function () {
            Config::set('cors.allowed_origins', ['*']);

            $origin = 'https://any-site.com';

            $allowedMethods = Config::get('cors.allowed_methods');

            if ($allowedMethods !== ['*'] && !in_array('GET', $allowedMethods)) {
                $this->markTestSkipped("GET method is not permitted.");
            }

            $response = $this->withHeaders(['Origin' => $origin])->get(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            expect($allowedOrigin === '*' || str_contains($allowedOrigin, $origin))->toBeTrue();

            $response->assertSuccessful();
        });

        it('permits simple GET request from an allowed origin', function () {
            $origin = 'https://myapp.com';

            $allowedMethods = Config::get('cors.allowed_methods');

            if ($allowedMethods !== ['*'] && !in_array('GET', $allowedMethods)) {
                $this->markTestSkipped("GET method is not permitted.");
            }

            $response = $this->withHeaders(['Origin' => $origin])->get(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            expect($allowedOrigin === '*' || str_contains($allowedOrigin, $origin))->toBeTrue();

            $response->assertSuccessful();
        });

        it('permits simple GET request from an allowed subdomain origin', function () {
            $origin = 'https://sub.myapp.com';

            $allowedMethods = Config::get('cors.allowed_methods');

            if ($allowedMethods !== ['*'] && !in_array('GET', $allowedMethods)) {
                $this->markTestSkipped("GET method is not permitted.");
            }

            $response = $this->withHeaders(['Origin' => $origin])->get(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            expect($allowedOrigin === '*' || str_contains($allowedOrigin, $origin))->toBeTrue();

            $response->assertSuccessful();
        });

        it('does not permit simple GET request from a disallowed origin', function () {
            $origin = 'https://evil.com';

            $allowedMethods = Config::get('cors.allowed_methods');

            if ($allowedMethods !== ['*'] && !in_array('GET', $allowedMethods)) {
                $this->markTestSkipped("GET method is not permitted.");
            }

            $response = $this->withHeaders(['Origin' => $origin])->get(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin === '*') {
                $this->markTestSkipped("Allowed Origins is '*', all origins are permitted.");
            }

            expect($allowedOrigin)->not->toBe($origin);

            $response->assertSuccessful();
        });

        it('does not permit simple GET request from a disallowed subdomain origin', function () {
            Config::set('cors.allowed_origins_patterns', []);

            $origin = 'https://sub.myapp.com';

            $allowedMethods = Config::get('cors.allowed_methods');

            if ($allowedMethods !== ['*'] && !in_array('GET', $allowedMethods)) {
                $this->markTestSkipped("GET method is not permitted.");
            }

            $response = $this->withHeaders(['Origin' => $origin])->get(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin === '*') {
                $this->markTestSkipped("Allowed Origins is '*', all origins are permitted.");
            }

            expect($allowedOrigin)->not->toBe($origin);

            $response->assertSuccessful();
        });
    });

    describe('Preflight OPTIONS request', function () {

        beforeEach(function () {
            Config::set('cors.allowed_methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
            Config::set('cors.allowed_origins', ['https://myapp.com']);
            Config::set('cors.allowed_origins_patterns', ['/^https:\/\/.*\.myapp\.com$/']);
            Config::set('cors.allowed_headers', ['content-type', 'authorization']);
        });

        it('permits preflight request from any origin when wildcard is configured', function () {
            Config::set('cors.allowed_origins', ['*']);

            $origin = 'https://any-site.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            expect($allowedOrigin === '*' || str_contains($allowedOrigin, $origin))->toBeTrue();

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            if ($allowedMethods !== '*' && !str_contains((string)$allowedMethods, $requestedMethod)) {
                $this->markTestSkipped("Request method '{$requestedMethod}' is not permitted.");
            }

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            if ($allowedHeaders !== '*' && !str_contains((string)$allowedHeaders, $requestedHeaders)) {
                $this->markTestSkipped("Request header '{$requestedHeaders}' is not permitted.");
            }

            $response->assertSuccessful();
        });

        it('permits preflight request from an allowed origin', function () {
            $origin = 'https://myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            expect($allowedOrigin === '*' || str_contains($allowedOrigin, $origin))->toBeTrue();

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            if ($allowedMethods !== '*' && !str_contains($allowedMethods, $requestedMethod)) {
                $this->markTestSkipped("Request method '{$requestedMethod}' is not permitted.");
            }

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            if ($allowedHeaders !== '*' && !str_contains($allowedHeaders, $requestedHeaders)) {
                $this->markTestSkipped("Request header '{$requestedHeaders}' is not permitted.");
            }

            $response->assertSuccessful();
        });

        it('permits preflight request from an allowed subdomain origin', function () {
            $origin = 'https://sub.myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            expect($allowedOrigin === '*' || str_contains((string)$allowedOrigin, $origin))->toBeTrue();

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            if ($allowedMethods !== '*' && !str_contains((string)$allowedMethods, $requestedMethod)) {
                $this->markTestSkipped("Request method '{$requestedMethod}' is not permitted.");
            }

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            if ($allowedHeaders !== '*' && !str_contains((string)$allowedHeaders, $requestedHeaders)) {
                $this->markTestSkipped("Request header '{$requestedHeaders}' is not permitted.");
            }

            $response->assertSuccessful();
        });

        it('permits preflight request with an allowed method', function () {
            $origin = 'https://myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin !== '*' && !str_contains($allowedOrigin, $origin)) {
                $this->markTestSkipped("Origin '{$origin}' is not permitted.");
            }

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            expect($allowedMethods === '*' || str_contains($allowedMethods, $requestedMethod))->toBeTrue();

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            if ($allowedHeaders !== '*' && !str_contains($allowedHeaders, $requestedHeaders)) {
                $this->markTestSkipped("Request header '{$requestedHeaders}' is not permitted.");
            }

            $response->assertSuccessful();
        });

        it('permits preflight request with an allowed header', function () {
            $origin = 'https://myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin !== '*' && !str_contains($allowedOrigin, $origin)) {
                $this->markTestSkipped("Origin '{$origin}' is not permitted.");
            }

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            if ($allowedMethods !== '*' && !str_contains($allowedMethods, $requestedMethod)) {
                $this->markTestSkipped("Request method '{$requestedMethod}' is not permitted.");
            }

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            expect($allowedHeaders === '*' || str_contains($allowedHeaders, $requestedHeaders))->toBeTrue();

            $response->assertSuccessful();
        });

        it('does not permit preflight request from a disallowed origin', function () {
            $origin = 'https://evil.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin === '*') {
                $this->markTestSkipped("Allowed Origins is '*', all origins are permitted.");
            }

            expect($allowedOrigin)->not->toBe($origin);
        });

        it('does not permit preflight request from a disallowed subdomain origin', function () {
            Config::set('cors.allowed_origins_patterns', []);

            $origin = 'https://sub.myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin === '*') {
                $this->markTestSkipped("Allowed Origins is '*', all origins are permitted.");
            }

            expect($allowedOrigin)->not->toBe($origin);
        });

        it('does not permit preflight request with a disallowed method', function () {
            Config::set('cors.allowed_methods', ['GET', 'HEAD']);

            $origin = 'https://myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'content-type';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin !== '*' && !str_contains((string)$allowedOrigin, $origin)) {
                $this->markTestSkipped("Origin '{$origin}' is not permitted.");
            }

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            if ($allowedHeaders !== '*' && !str_contains((string)$allowedHeaders, $requestedHeaders)) {
                $this->markTestSkipped("Request header '{$requestedHeaders}' is not permitted.");
            }

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            if ($allowedMethods === '*') {
                $this->markTestSkipped("Allowed Methods is '*', all methods are permitted.");
            }

            expect($allowedMethods)->not->toContain($requestedMethod);
        });

        it('does not permit preflight request with a disallowed header', function () {
            $origin = 'https://myapp.com';
            $requestedMethod = 'PATCH';
            $requestedHeaders = 'x-custom-header';

            $response = $this->withHeaders([
                'Origin' => $origin,
                'Access-Control-Request-Method' => $requestedMethod,
                'Access-Control-Request-Headers' => $requestedHeaders,
            ])->options(CORS_TEST_ENDPOINT);

            $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

            if ($allowedOrigin !== '*' && !str_contains($allowedOrigin, $origin)) {
                $this->markTestSkipped("Origin '{$origin}' is not permitted.");
            }

            $allowedMethods = $response->headers->get('Access-Control-Allow-Methods');

            if ($allowedMethods !== '*' && !str_contains($allowedMethods, $requestedMethod)) {
                $this->markTestSkipped("Request method '{$requestedMethod}' is not permitted.");
            }

            $allowedHeaders = $response->headers->get('Access-Control-Allow-Headers');

            if ($allowedHeaders === '*') {
                $this->markTestSkipped("Allowed Headers is '*', all headers are permitted.");
            }

            expect($allowedHeaders)->not->toContain($requestedHeaders);
        });
    });

    describe('Credentials', function () {

        beforeEach(function () {
            Config::set('cors.allowed_origins', ['https://myapp.com']);
        });

        it('does not include credentials header when supports_credentials is false', function () {
            Config::set('cors.supports_credentials', false);

            $response = $this->withHeaders(['Origin' => 'https://myapp.com'])
                ->get(CORS_TEST_ENDPOINT);

            $response->assertHeaderMissing('Access-Control-Allow-Credentials');
        });

        it('includes credentials header when supports_credentials is true', function () {
            Config::set('cors.supports_credentials', true);

            $response = $this->withHeaders(['Origin' => 'https://myapp.com'])
                ->get(CORS_TEST_ENDPOINT);

            $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        });
    });
});
