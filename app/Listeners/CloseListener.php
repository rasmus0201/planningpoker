<?php

namespace App\Listeners;

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
        }
    }
}
