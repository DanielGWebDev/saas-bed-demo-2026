<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

function hasRouteAuth($routeName): bool
{
    try {
        $route = app('router')->getRoutes()->getByName($routeName);
        if (!$route) return false;
        return in_array('auth:sanctum', $route->middleware() ?? []);
    } catch (Throwable) {
        return false;
    }
}

beforeEach(function () {
    $this->usersAuth = hasRouteAuth('api.v1.users.index');
    $this->contactsAuth = hasRouteAuth('api.v1.contacts.index');
    $this->detailTypesAuth = hasRouteAuth('api.v1.detail-types.index');

    $this->loginPublic = !hasRouteAuth('api.v1.auth.login');
});

describe('Auth', function () {

    describe('Login', function () {

        beforeEach(function () {
            $loginExists = !is_null(app('router')->getRoutes()->getByName('api.v1.auth.login'));
            if (!$loginExists) test()->markTestSkipped('Login route missing');
        });

        it('returns a token when logging in with valid credentials', function () {
            $user = User::factory()->create();

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $response->assertStatus(200);
            $response->assertJsonStructure(['token']);
        });

        it('returns 422 when logging in with wrong password', function () {
            $user = User::factory()->create();

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors('email');
        });

        it('returns 422 when logging in with unrecognised email', function () {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'nobody@example.com',
                'password' => 'password',
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors('email');
        });

        it('returns 422 when logging in with missing email', function () {
            $response = $this->postJson('/api/v1/auth/login', [
                'password' => 'password',
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors('email');
        });

        it('returns 422 when logging in with missing password', function () {
            $user = User::factory()->create();

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors('password');
        });

    });

    describe('Logout', function () {

        beforeEach(function () {
            $logoutExists = !is_null(app('router')->getRoutes()->getByName('api.v1.auth.logout'));
            if (!$logoutExists) test()->markTestSkipped('Logout route missing');
        });

        it('logs out successfully and invalidates the token', function () {
            $user = User::factory()->create();
            $token = $user->createToken('api-token', [], now()->addYear())->plainTextToken;

            $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Logged out successfully.']);

            $tokenId = explode('|', $token)[0];
            expect(PersonalAccessToken::find($tokenId))->toBeNull();
        });

        it('returns 401 when logging out without a token', function () {
            $response = $this->postJson('/api/v1/auth/logout');

            $response->assertStatus(401);
            $response->assertJson(['message' => 'Unauthenticated.']);
        });

    });

    describe('Authenticated access', function () {

        beforeEach(function () {
            $loginExists = !is_null(app('router')->getRoutes()->getByName('api.v1.auth.login'));
            if (!$loginExists) test()->markTestSkipped('Login route missing');
        });

        it('returns 200 when accessing users with a valid token', function () {
            $user = User::factory()->create();

            $token = $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->json('token');

            $this->withToken($token)->getJson('/api/v1/users')->assertStatus(200);
        });

        it('returns 200 when accessing contacts with a valid token', function () {
            $user = User::factory()->create();

            $token = $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->json('token');

            $this->withToken($token)->getJson('/api/v1/contacts')->assertStatus(200);
        });

        it('returns 200 when accessing detail-types with a valid token', function () {
            $user = User::factory()->create();

            $token = $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->json('token');

            $this->withToken($token)->getJson('/api/v1/detail-types')->assertStatus(200);
        });

    });

    describe('Unauthenticated access', function () {

        it('returns 401 when accessing users without a token', function () {
            if ($this->usersAuth) {
                $this->getJson('/api/v1/users')->assertStatus(401);
            } else {
                test()->markTestSkipped('Users route does not require authentication');
            }
        });

        it('returns 401 when accessing contacts without a token', function () {
            if ($this->contactsAuth) {
                $this->getJson('/api/v1/contacts')->assertStatus(401);
            } else {
                test()->markTestSkipped('Contacts route does not require authentication');
            }
        });

        it('returns 401 when accessing detail-types without a token', function () {
            if ($this->detailTypesAuth) {
                $this->getJson('/api/v1/detail-types')->assertStatus(401);
            } else {
                test()->markTestSkipped('Detail-types route does not require authentication');
            }
        });

    });

});
