<?php

namespace App\Actions;

use App\Database;

class PublishAvailableUsers extends Action
{
    public function run()
    {
        $stmt = Database::run('SELECT username FROM users WHERE connected = 0');

        $users = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'username');

        $this->event->sendPublisher([
            'type' => 'users',
            'data' => [
                'users' => array_filter(array_unique($users)),
            ],
        ]);

        $this->event->sendSubscribers([
            'type' => 'users',
            'data' => [
                'users' => array_filter(array_unique($users)),
            ],
        ]);
    }
}
