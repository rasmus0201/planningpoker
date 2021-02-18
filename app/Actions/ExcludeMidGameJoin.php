<?php

namespace App\Actions;

use App\Database;

class ExcludeMidGameJoin extends Action
{
    public function run()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if (!$clientId) {
            return;
        }

       $votesCount = Database::run('SELECT COUNT(v.id) as count FROM votes v
            LEFT JOIN users u ON u.id = v.user_id
            WHERE u.connected = 1
            AND u.clientId != :clientId
            LIMIT 1',
            [':clientId' => $clientId]
        )->fetch(\PDO::FETCH_ASSOC);

        // If there is already votes in,
        // Then send a notification saying that you are waiting for results
        if (intval($votesCount['count']) > 0) {
            // Set exclude flag for user
            Database::run(
                'UPDATE users SET excluded = 1 WHERE clientId = :clientId',
                [':clientId' => $clientId]
            );

            $this->event->sendSubscribers([
                'type' => 'excluded',
                'data' => [
                    'username' => $this->event->data['data']['username']
                ]
            ]);

            $this->event->sendPublisher([
                'type' => 'midgame_join',
                'data' => []
            ]);
        }
    }
}
