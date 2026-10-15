<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $this->ensureSafeTestingDatabaseConfiguration();

        return parent::createApplication();
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

        if ($database === ':memory:') {
            return;
        }

        $normalizedDatabase = str_replace('\\', '/', $database);
        if (! str_ends_with($normalizedDatabase, 'testing.sqlite')) {
            throw new RuntimeException("Configuración insegura: DB_DATABASE de tests debe apuntar a 'testing.sqlite'. Valor actual: {$database}");
        }

        $databasePath = $this->toAbsolutePath($database);
        $directory = dirname($databasePath);

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException("No se pudo crear el directorio para la base de pruebas: {$directory}");
        }

        if (! file_exists($databasePath) && @touch($databasePath) === false) {
            throw new RuntimeException("No se pudo crear la base de pruebas: {$databasePath}");
        }
    }

    protected function envValue(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? null : (string) $value;
    }

    protected function toAbsolutePath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return $path;
        }

        return dirname(__DIR__).DIRECTORY_SEPARATOR.ltrim($path, '\\/');
    }
}
