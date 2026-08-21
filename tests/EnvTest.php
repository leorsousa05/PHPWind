<?php

declare(strict_types=1);

namespace PHPWind\Tests;

use PHPUnit\Framework\TestCase;
use PHPWind\Config\Env;

class EnvTest extends TestCase
{
    public function testReturnsEnvironmentVariableWhenSet(): void
    {
        $key = 'PHPWIND_ENV_TEST_' . uniqid();
        putenv("{$key}=foo");

        try {
            $this->assertSame('foo', Env::get($key, 'default'));
        } finally {
            putenv($key);
        }
    }

    public function testFallsBackToServerWhenEnvNotSet(): void
    {
        $key = 'PHPWIND_ENV_TEST_' . uniqid();
        $_SERVER[$key] = 'from-server';

        try {
            $this->assertSame('from-server', Env::get($key, 'default'));
        } finally {
            unset($_SERVER[$key]);
        }
    }

    public function testReturnsDefaultWhenMissingEverywhere(): void
    {
        $key = 'PHPWIND_ENV_TEST_MISSING_' . uniqid();
        $this->assertSame('default', Env::get($key, 'default'));
    }

    public function testReturnsNullDefaultWhenMissing(): void
    {
        $key = 'PHPWIND_ENV_TEST_MISSING_' . uniqid();
        $this->assertNull(Env::get($key));
    }
}
