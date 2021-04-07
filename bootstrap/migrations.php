<?php

require_once __DIR__ . '/index.php';

/** @var \App\Migrator */
$migrator = $app->make(\App\Migrator::class);

if ($migrator->shouldMigrate()) {
    $migrator->migrate([
        'CreateConnectionsTable',
        'CreateUsersTable',
        'CreateGamesTable',
        'CreateGameRoundsTable',
        'CreateGameVotesTable'
    ]);
}

$seeder = new \App\Seeder();
$seeder->run();
