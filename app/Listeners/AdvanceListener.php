<?php

namespace App\Listeners;

use App\Database;

class AdvanceListener extends Listener
{
    public function listen()
    {
        return 'message:advance';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if (!$clientId) {
            return;
        }

        $stmt = Database::run(
            'SELECT * FROM users WHERE clientId = :clientId LIMIT 1',
            [':clientId' => $clientId]
        );

        if (!$user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return;
        }

        Database::run('UPDATE users SET advanced = 1 WHERE id = :id', [':id' => $user['id']]);

        $counts = Database::run(
            'SELECT COUNT(*) user_count, SUM(advanced) as advanced_count FROM users WHERE connected = :connected and clientId IS NOT NULL',
            [':connected' => 1]
        )->fetch(\PDO::FETCH_ASSOC);

        if ($counts['user_count'] === $counts['advanced_count']) {
            Database::run('DELETE FROM votes');
            Database::run('UPDATE users SET advanced = 0');

            $this->event->sendPublisher([
                'type' => 'finish',
                'data' => [
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);

            $this->event->sendSubscribers([
                'type' => 'finish',
                'data' => [
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        }
    }
}
