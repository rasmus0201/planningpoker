<?php

namespace App\Listeners;

use App\Database;

class JoinListener extends Listener
{
    public function listen()
    {
        return 'message:join';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if (!$clientId) {
            return;
        }

        $stmt = Database::run(
            'SELECT * FROM users WHERE username = :username LIMIT 1',
            [
                ':username' => $this->event->data['data']['username'] ?? ''
            ]
        );

        if (!$user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return;
        }

        $currentRound = Database::run('SELECT round_id FROM users ORDER BY round_id DESC LIMIT 1')->fetch(\PDO::FETCH_ASSOC)['round_id'];
        if ($currentRound === null) {
            $currentRound = 0;
        }

        Database::run(
            'UPDATE users SET resourceId = :resourceId, :round_id = :round_id, clientId = :clientId, connected = 1 WHERE username = :username',
            [
                ':resourceId' => $this->event->getPublisher()->resourceId,
                ':round_id' => $currentRound,
                ':clientId' => $clientId,
                ':username' => $user['username'],
            ]
        );

        $users = Database::run(
            'SELECT username FROM users WHERE connected = :connected',
            [':connected' => 1]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $votes = Database::run("SELECT u.username FROM votes v
            LEFT JOIN users u ON u.id = v.user_id
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $this->event->sendPublisher([
            'type' => 'login',
            'data' => [
                'session' => [
                    'clientId' => $clientId,
                    'username' => $user['username'],
                    'round_id' => $currentRound,
                    'auth' => true,
                ],
                'joined' => array_filter(array_unique(array_column($users, 'username'))),
                'votes' => array_filter(array_unique(array_column($votes, 'username'))),
            ]
        ]);

        $this->event->sendSubscribers([
            'type' => 'join',
            'data' => [
                'username' => $user['username'],
            ]
        ]);

        $this->publishAvailableUsers();
    }

    private function publishAvailableUsers()
    {
        $stmt = Database::run(
            'SELECT username FROM users WHERE connected = :connected',
            [':connected' => 0]
        );

        $users = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'username');

        $this->event->sendPublisher([
            'type' => 'users',
            'data' => [
                'users' => array_filter(array_unique($users)),
            ],
        ]);

        $this->event->sendSubscribers([
            'type' => 'users',
            'data' => [
                'users' => array_filter(array_unique($users)),
            ],
        ]);
    }
}
