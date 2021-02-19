<?php

namespace App\Repositories;

use App\Database;
use PDO;

class VoteRepository extends AbstractRepository
{
    public function getVotes()
    {
        return Database::run('SELECT u.username, v.vote_id FROM votes v
            INNER JOIN users u ON u.id = v.user_id
            WHERE u.connected = 1
            AND u.is_excluded = 0
            AND u.clientId IS NOT NULL
            ORDER BY u.id
        ')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertVote($userId, $voteId)
    {
        Database::run(
            'INSERT INTO votes (user_id, vote_id) VALUES (:user_id, :vote_id)',
            [
                ':user_id' => $userId,
                ':vote_id' => $voteId,
            ]
        );
    }

    public function countVotesExcludeByClientId($clientId)
    {
        $votesCount = Database::run('SELECT COUNT(v.id) as count FROM votes v
            LEFT JOIN users u ON u.id = v.user_id
            WHERE u.connected = 1
            AND u.clientId != :clientId
            LIMIT 1',
            [':clientId' => $clientId]
        )->fetch(PDO::FETCH_ASSOC);

        return (int) $votesCount['count'];
    }

    public function deleteByUserId($userId)
    {
        Database::run(
            'DELETE FROM votes WHERE user_id = :userId',
            [':userId', $userId]
        );
    }

    public function deleteAll()
    {
        Database::run('DELETE FROM votes');
    }
}
