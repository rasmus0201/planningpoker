<?php

namespace App\Listeners;

use App\Actions\ShowOff;
use App\Database;

class VoteListener extends Listener
{
    public function listen()
    {
        return 'message:vote';
    }

    public function handle()
    {
        $stmt = Database::run(
            'SELECT * FROM users WHERE clientId = :clientId LIMIT 1',
            [
                ':clientId' => $this->event->data['data']['clientId'] ?? ''
            ]
        );

        if (!$user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return;
        }

        Database::run('INSERT INTO votes (user_id, vote_id) VALUES (:user_id, :vote_id)', [
            ':user_id' => (int) $user['id'],
            ':vote_id' => (int) $this->event->data['data']['vote'],
        ]);

        $this->event->sendSubscribers([
            'type' => 'vote',
            'data' => [
                'username' => $this->event->data['data']['username'] ?? '',
            ]
        ]);

        $this->sendShowoffIfAllVoted();
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);

        $action->run();
    }
}
