<?php

declare(strict_types=1);

namespace App;

use App\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as Schema;

class Migrator
{
    private Application $app;
    private Connection $db;
    private Schema $schema;

    public function __construct(
        Application $app,
        Connection $db,
        Schema $schema
    ) {
        $this->app = $app;
        $this->db = $db;
        $this->schema = $schema;
    }

    public function shouldMigrate(): bool
    {
        $database = $this->db->getConfig('database');

        $migrationTable = $this->db->select('SELECT *
            FROM `information_schema`.`TABLES` t
            WHERE t.`table_schema` = :db
                AND t.`table_name` = :tableName
            LIMIT 1', [
            'db' => $database,
            'tableName' => 'migrations'
        ]);

        return empty($migrationTable);
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
