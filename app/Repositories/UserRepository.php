<?php

namespace App\Repositories;

use App\Database;
use PDO;

class UserRepository extends AbstractRepository
{
    public function getUnconnectedUsers()
    {
        return Database::run(
            'SELECT * FROM users WHERE connected = 0'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConnectedUsers()
    {
        return Database::run(
            'SELECT * FROM users WHERE connected = 1 AND clientId IS NOT NULL'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUsername($username)
    {
        return Database::run(
            'SELECT * FROM users WHERE username = :username LIMIT 1',
            [':username' => $username]
        )->fetch(PDO::FETCH_ASSOC);
    }

    public function getByClientId($clientId)
    {
        return Database::run(
            'SELECT * FROM users WHERE clientId = :clientId LIMIT 1',
            [':clientId' => $clientId]
        )->fetch(PDO::FETCH_ASSOC);
    }

    public function getByResourceId($resourceId)
    {
        return Database::run(
            'SELECT * FROM users WHERE resourceId = :resourceId LIMIT 1',
            [':resourceId' => $resourceId]
        )->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsersThatVoted()
    {
        return Database::run('SELECT u.* FROM votes v
                LEFT JOIN users u ON u.id = v.user_id
                WHERE u.connected = 1
                AND u.clientId IS NOT NULL
        ')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countVotingUsers()
    {
        $countUsers = Database::run('SELECT COUNT(*) as count
            FROM users
            WHERE connected = 1
            AND clientId IS NOT NULL
            AND is_excluded = 0
        ')->fetch(PDO::FETCH_ASSOC);

        return (int) $countUsers['count'];
    }

    public function countAdvancedUsers()
    {
        $counts = Database::run('SELECT COUNT(*) user_count, SUM(is_advanced) as advanced_count
            FROM users
            WHERE connected = 1
            AND is_excluded = 0
            AND clientId IS NOT NULL
        ')->fetch(PDO::FETCH_ASSOC);

        return array_map('intval', $counts);
    }

    public function setConnectedById($id, $resourceId)
    {
        Database::run(
            'UPDATE users SET resourceId = :resourceId, connected = 1 WHERE id = :id',
            [
                ':id' => $id,
                ':resourceId' => $resourceId,
            ]
        );
    }

    public function setUnconnectedById($id)
    {
        Database::run(
            'UPDATE users SET resourceId = NULL, connected = 0 WHERE id = :id',
            [':id' => $id]
        );
    }

    public function setClientIdById($id, $clientId)
    {
        Database::run(
            'UPDATE users SET clientId = :clientId WHERE id = :id',
            [
                ':id' => $id,
                ':clientId' => $clientId
            ]
        );
    }

    public function setAdvancedById($id, $advanced)
    {
        Database::run(
            'UPDATE users SET is_advanced = :advanced WHERE id = :id',
            [
                ':id' => $id,
                ':advanced' => (int) $advanced
            ]
        );
    }

    public function setExcludedById($id, $excluded)
    {
        Database::run(
            'UPDATE users SET is_excluded = :isExcluded WHERE id = :id',
            [
                ':id' => $id,
                ':isExcluded' => (int) $excluded
            ]
        );
    }

    public function setExcludedByClientId($clientId, $excluded)
    {
        Database::run(
            'UPDATE users SET is_excluded = :isExcluded WHERE clientId = :clientId',
            [
                ':clientId' => $clientId,
                ':isExcluded' => (int) $excluded
            ]
        );
    }

    public function resetAdvancedAll()
    {
        Database::run('UPDATE users SET is_advanced = 0');
    }

    public function resetExcludedAll()
    {
        Database::run('UPDATE users SET is_excluded = 0');
    }
}
