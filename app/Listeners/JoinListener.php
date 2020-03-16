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

        Database::run(
            'UPDATE users SET connected = 1, clientId = :clientId WHERE username = :username',
            [
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
                    'auth' => true,
                ],
                'joined' => array_column($users, 'username'),
                'votes' => array_column($votes, 'username'),
            ]
        ]);

        $this->event->sendSubscribers([
            'type' => 'join',
            'data' => [
                'username' => $user['username'],
            ]
        ]);
    }
}
