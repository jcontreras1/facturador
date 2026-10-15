<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $this->forceTestingCachePaths();
        $this->ensureSafeTestingDatabaseConfiguration();

        $app = parent::createApplication();

        $this->ensureRuntimeDatabaseIsSafe($app);

        return $app;
    }

    protected function ensureSafeTestingDatabaseConfiguration(): void
    {
        $appEnv = $this->envValue('APP_ENV') ?? 'testing';
        if ($appEnv !== 'testing') {
            return;
        }

        $connection = $this->envValue('DB_CONNECTION') ?? 'sqlite';
        if ($connection !== 'sqlite') {
            throw new RuntimeException("Configuración insegura: DB_CONNECTION para tests debe ser 'sqlite'.");
        }

        $database = $this->envValue('DB_DATABASE');
        if (! is_string($database) || trim($database) === '') {
            throw new RuntimeException("Configuración insegura: DB_DATABASE de tests no está definido.");
        }

        if ($database !== ':memory:') {
            throw new RuntimeException("Configuración insegura: DB_DATABASE para tests debe ser ':memory:'. Valor actual: {$database}");
        }
    }

    protected function forceTestingCachePaths(): void
    {
        $cachePaths = [
            'APP_CONFIG_CACHE' => 'bootstrap/cache/testing-config.php',
            'APP_ROUTES_CACHE' => 'bootstrap/cache/testing-routes.php',
            'APP_EVENTS_CACHE' => 'bootstrap/cache/testing-events.php',
            'APP_PACKAGES_CACHE' => 'bootstrap/cache/testing-packages.php',
            'APP_SERVICES_CACHE' => 'bootstrap/cache/testing-services.php',
        ];

        foreach ($cachePaths as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }
    }

    protected function ensureRuntimeDatabaseIsSafe($app): void
    {
        $defaultConnection = (string) $app['config']->get('database.default');
        if ($defaultConnection !== 'sqlite') {
            throw new RuntimeException("Configuración insegura en runtime: conexión por defecto no es sqlite ({$defaultConnection}).");
        }

        $configuredDatabase = (string) $app['config']->get('database.connections.sqlite.database');
        if ($configuredDatabase !== ':memory:') {
            throw new RuntimeException("Configuración insegura en runtime: sqlite.database debe ser ':memory:' y se recibió '{$configuredDatabase}'.");
        }

        $cachedConfigPath = (string) $this->envValue('APP_CONFIG_CACHE');
        $defaultCachedConfigPath = dirname(__DIR__).DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'config.php';
        if ($cachedConfigPath === '' || $cachedConfigPath === $defaultCachedConfigPath) {
            throw new RuntimeException('Configuración insegura: APP_CONFIG_CACHE no está aislado para pruebas.');
        }
    }

    protected function envValue(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? null : (string) $value;
    }
}
