<?php

declare(strict_types=1);

namespace App;

use App\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as Schema;

class Migrator
{
    private Application $app;
    private Schema $schema;

    public function __construct(
        Application $app,
        Schema $schema
    ) {
        $this->app = $app;
        $this->schema = $schema;
    }

    public function dropAll(): void
    {
        $this->schema->dropAllTables();
    }

    public function shouldMigrate(): bool
    {
        return !$this->schema->hasTable('migrations');
    }

    /**
     * @param string[] $migrations
     */
    public function migrate(array $migrations): void
    {
        foreach($migrations as $migrationName) {
            $this->requireMigration($migrationName);

            $migration = $this->app->make($migrationName);

            if (method_exists($migration, 'up')) {
                $migration->up();
            }
        }

        $this->createMigrationsTable();
    }

    /**
     * @param string[] $migrations
     */
    public function refresh(array $migrations): void
    {
        $this->removeMigrationsTable();

        foreach($migrations as $migrationName) {
            $this->requireMigration($migrationName);

            $migration = $this->app->make($migrationName);

            if (method_exists($migration, 'down')) {
                $migration->down();
            }

            if (method_exists($migration, 'up')) {
                $migration->up();
            }
        }

        $this->createMigrationsTable();
    }

    private function requireMigration(string $migrationName)
    {
        $migration = $this->app->basePath('migrations/' . $migrationName . '.php');

        if (!file_exists($migration)) {
            throw new \Exception('Migration file does not exist');
        }

        require_once $migration;
    }

    private function createMigrationsTable(): void
    {
        $this->schema->create('migrations', function (Blueprint $table) {
            $table->id();
        });
    }

    private function removeMigrationsTable(): void
    {
        $this->schema->dropIfExists('migrations');
    }
}
