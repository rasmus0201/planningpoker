<?php

namespace App;

use PDO;

class Database
{
    CONST DATABASE_PATH = __DIR__ . '/../db.sqlite';

    /**
     * @var Database
     */
    private static $instance = null;

    /**
     * @var \PDO
     */
    private $dbh;

    private $allUsers = [
        'jkc',
        'mng',
        'ofm',
        'rby',
        'rso',
        'sse',
    ];

    private function __construct()
    {
        $path = self::DATABASE_PATH;
        $shouldMigrate = !file_exists($path);

        $this->dbh = new PDO("sqlite:{$path}");
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        if ($this->dbh && $shouldMigrate) {
            $this->dbh->exec("CREATE TABLE IF NOT EXISTS votes (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                vote_id INTEGER
            )");

            $this->dbh->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY,
                clientId TEXT,
                username TEXT NOT NULL UNIQUE,
                connected INTEGER
            )");

            foreach ($this->allUsers as $user) {
                $stmt = $this->dbh->prepare("INSERT INTO users (username, connected) VALUES (:username, 0)");
                $stmt->execute([
                    ':username' => $user
                ]);
            }
        }
    }

    private function __clone() {}

    public static function instance()
    {
        if (self::$instance === null)
        {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array([
            self::instance()->dbh,
            $method
        ], $args);
    }

    public static function run($sql, $args = [])
    {
        if (!$args) {
            return self::instance()->dbh->query($sql);
        }

        $stmt = self::instance()->dbh->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }
}
