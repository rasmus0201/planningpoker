<?php

namespace App\Actions;

use App\Database;

class Login extends Action
{
    public function run()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

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

            $users = Database::run('SELECT username FROM users WHERE connected = 1 and clientId IS NOT NULL')
                ->fetchAll(\PDO::FETCH_ASSOC);

            $votes = Database::run('SELECT u.username FROM votes v
                LEFT JOIN users u ON u.id = v.user_id
                WHERE u.connected = 1
            ')->fetchAll(\PDO::FETCH_ASSOC);

            $this->event->sendPublisher([
                'type' => 'login',
                'data' => [
                    'session' => [
                        'clientId' => $clientId,
                        'username' => $user['username'],
                        'advanced' => (bool) $user['advanced'],
                        'midgame_join' => (bool) $user['excluded'],
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

            $this->sendShowoffIfAllVoted();
        }
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);
        $action->run();
    }
}
