<?php

namespace App\Actions;

use App\Database;

class ShowOff extends Action
{
    public function run()
    {
        // Check if last vote, if so send the answers.
        $countConnectedUsers = Database::run('SELECT COUNT(*) as count
            FROM users
            WHERE connected = 1
            AND clientId IS NOT NULL
            AND excluded = 0
        ')->fetch(\PDO::FETCH_ASSOC);

        $votes = Database::run('SELECT u.username, v.vote_id FROM votes v
            INNER JOIN users u ON u.id = v.user_id
            WHERE u.connected = 1
            AND u.clientId IS NOT NULL
            AND u.excluded = 0
            ORDER BY u.id
        ')->fetchAll(\PDO::FETCH_ASSOC);

        $this->event->sendPublisher([
            'type' => 'test',
            'data' => [
                $votes,
                $countConnectedUsers
            ]
        ]);

        if (((int) $countConnectedUsers['count']) !== count($votes)) {
            return;
        }

        Database::run('UPDATE users SET excluded = 0');

        try {
            $this->event->sendSubscribers([
                'type' => 'showoff',
                'data' => $votes
            ]);
        } catch (\Throwable $th) {
        }

        try {
            $this->event->sendPublisher([
                'type' => 'showoff',
                'data' => $votes
            ]);
        } catch (\Throwable $th) {
        }
    }
}
