<?php

namespace App\Actions;

use App\Database;

class FinishRound extends Action
{
    public function run()
    {
        $counts = Database::run(
            'SELECT COUNT(*) user_count, SUM(advanced) as advanced_count FROM users WHERE connected = :connected and clientId IS NOT NULL',
            [':connected' => 1]
        )->fetch(\PDO::FETCH_ASSOC);

        if ($counts['user_count'] !== $counts['advanced_count']) {
            return;
        }

        Database::run('DELETE FROM votes');
        Database::run('UPDATE users SET advanced = 0');

        try {
            $this->event->sendSubscribers([
                'type' => 'finish',
                'data' => [
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        } catch (\Throwable $th) {
        }

        try {
            $this->event->sendPublisher([
                'type' => 'finish',
                'data' => [
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        } catch (\Throwable $th) {
        }
    }
}
