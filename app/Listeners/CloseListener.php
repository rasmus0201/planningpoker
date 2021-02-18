<?php

namespace App\Listeners;

use App\Actions\FinishRound;
use App\Actions\ShowOff;
use App\Database;

class CloseListener extends Listener
{
    public function listen()
    {
        return 'close';
    }

    public function handle()
    {
        $stmt = Database::run(
            'SELECT * FROM users WHERE resourceId = :resourceId LIMIT 1',
            [':resourceId' => $this->event->getPublisher()->resourceId]
        );

        if ($user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            Database::run(
                'UPDATE users SET connected = 0, resourceId = NULL WHERE id = :id',
                [':id' => $user['id']]
            );

            Database::run(
                'DELETE FROM votes WHERE user_id = :userId',
                [':userId', $user['id']]
            );

            $users = Database::run(
                'SELECT username FROM users WHERE connected = 1 and clientId IS NOT NULL'
            )->fetchAll(\PDO::FETCH_ASSOC);

            $votes = Database::run('SELECT u.username FROM votes v
                LEFT JOIN users u ON u.id = v.user_id
                WHERE u.connected = 1
            ')->fetchAll(\PDO::FETCH_ASSOC);

            $this->event->sendSubscribers([
                'type' => 'leave',
                'data' => [
                    'joined' => array_filter(array_unique(array_column($users, 'username'))),
                    'votes' => array_filter(array_unique(array_column($votes, 'username'))),
                ]
            ]);

            // Check to see if we should make a show off.
            $this->sendShowoffIfAllVoted();

            // Check to see if we should finish the round, and begin a new one.
            $this->finishRound();
        }
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);

        $action->run();
    }

    private function finishRound()
    {
        $action = new FinishRound($this->event);

        $action->run();
    }
}
