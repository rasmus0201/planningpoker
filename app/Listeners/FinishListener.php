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

        Database::run('DELETE FROM votes WHERE user_id = :user_id AND round_id = :round_id',
            [
                ':user_id' => $user['id'],
                ':round_id' => (int) $user['round_id']
            ]
        );

        $nextRound = ((int) $user['round_id']) + 1;

        Database::run(
            'UPDATE users SET round_id = :round_id WHERE id = :id',
            [
                ':round_id' => $nextRound,
                ':id' => $user['id']
            ]
        );

        $stmt = Database::run('SELECT COUNT(*) as count FROM votes WHERE round_id = :last_round_id', [':last_round_id' => $user['round_id']]);
        if (( (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'] ) === 0) {
            $this->event->sendPublisher([
                'type' => 'finish',
                'data' => [
                    'round_id' => (int) $nextRound,
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);

            $this->event->sendSubscribers([
                'type' => 'finish',
                'data' => [
                    'round_id' => (int) $nextRound,
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        }
    }
}
