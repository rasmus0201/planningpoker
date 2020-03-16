<?php

namespace App\Listeners;

use App\Database;

class FinishListener extends Listener
{
    public function listen()
    {
        return 'message:finish';
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

        $user['round_id']++;

        Database::run('DELETE FROM votes WHERE user_id = :user_id', [':user_id' => $user['id']]);

        Database::run(
            'UPDATE users SET :round_id = :round_id WHERE id = :id',
            [
                ':round_id' => $user['round_id'],
                ':id' => $user['id']
            ]
        );

        $stmt = Database::run('SELECT COUNT(*) as count FROM votes');
        if (( (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'] ) === 0) {
            $this->sendPublisher([
                'type' => 'finish',
                'data' => [
                    'round_id' => $user['round_id'],
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);

            $this->sendSubscribers([
                'type' => 'finish',
                'data' => [
                    'round_id' => $user['round_id'],
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        }
    }
}
