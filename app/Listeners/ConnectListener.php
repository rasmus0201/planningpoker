<?php

namespace App\Listeners;

use App\Database;

class ConnectListener extends Listener
{
    public function listen()
    {
        return 'message:connect';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if ($clientId) {
            $stmt = Database::run(
                'SELECT * FROM users WHERE clientId = :clientId LIMIT 1',
                [':clientId' => $clientId]
            );

            if ($user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                Database::run(
                    'UPDATE users SET connected = 1 WHERE id = :id',
                    [':id' => $user['id']]
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

                return;
            }
        }

        $this->publishUsers();
    }

    private function publishUsers()
    {
        $stmt = Database::run(
            'SELECT username FROM users WHERE connected = :connected',
            [':connected' => 0]
        );

        $users = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'username');

        $this->event->getPublisher()->send(json_encode([
            'type' => 'users',
            'data' => [
                'users' => $users,
            ],
        ]));
    }
}
