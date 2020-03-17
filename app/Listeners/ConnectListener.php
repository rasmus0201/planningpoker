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
                    'UPDATE users SET resourceId = :resourceId, connected = 1 WHERE id = :id',
                    [
                        ':resourceId' => $this->event->getPublisher()->resourceId,
                        ':id' => $user['id']
                    ]
                );

                $users = Database::run(
                    'SELECT username FROM users WHERE connected = :connected and clientId IS NOT NULL',
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
                            'advanced' => $user['advanced'],
                            'auth' => true,
                        ],
                        'joined' => array_filter(array_unique(array_column($users, 'username'))),
                        'votes' => array_filter(array_unique(array_column($votes, 'username'))),
                    ]
                ]);

                $this->sendShowoffIfAllVoted();

                $this->event->sendSubscribers([
                    'type' => 'join',
                    'data' => [
                        'username' => $user['username'],
                    ]
                ]);
            }
        }

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

    private function sendShowoffIfAllVoted()
    {
        // Check if last vote, if so send the answers.
        $countConnectedUsers = Database::run("SELECT COUNT(*) as count FROM users WHERE connected = 1 AND clientId IS NOT NULL")->fetch(\PDO::FETCH_ASSOC);
        $votes = Database::run("SELECT u.username, v.vote_id FROM votes v
            INNER JOIN users u ON u.id = v.user_id
            WHERE u.connected = 1
            AND u.clientId IS NOT NULL
            ORDER BY u.id
        ")->fetchAll(\PDO::FETCH_ASSOC);

        if (((int) $countConnectedUsers['count']) !== count($votes)) {
            return;
        }

        $this->event->sendPublisher([
            'type' => 'showoff',
            'data' => $votes
        ]);
    }
}
