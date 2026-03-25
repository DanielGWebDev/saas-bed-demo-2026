<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    // Wipe and rebuild the database between each test
    use RefreshDatabase;

    // Automatically run seeders before each test
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        // Reduce bcrypt rounds to speed up password hashing in tests
        Hash::setRounds(4);
    }
}
