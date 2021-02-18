<?php

namespace App\Listeners;

use App\Actions\FinishRound;
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

        $this->finishRound();
    }

    private function finishRound()
    {
        $action = new FinishRound($this->event);
        $action->run();
    }
}
