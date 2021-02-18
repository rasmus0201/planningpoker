<?php

namespace App\Listeners;

use App\Actions\ExcludeMidGameJoin;
use App\Actions\Login;
use App\Actions\PublishAvailableUsers;
use App\Database;

class JoinListener extends Listener
{
    public function listen()
    {
        return 'message:join';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if (!$clientId) {
            return;
        }

        $stmt = Database::run(
            'SELECT * FROM users WHERE username = :username LIMIT 1',
            [
                ':username' => $this->event->data['data']['username'] ?? ''
            ]
        );

        if (!$user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return;
        }

        Database::run(
            'UPDATE users SET advanced = 0, clientId = :clientId WHERE id = :id',
            [
                ':clientId' => $clientId,
                ':id' => $user['id'],
            ]
        );

        $loginAction = new Login($this->event);
        $loginAction->run();

        // If there is already votes in,
        // Then send a notification saying that you are waiting for results
        $excludeMidGameJoinAction = new ExcludeMidGameJoin($this->event);
        $excludeMidGameJoinAction->run();

        $publishAvailableUsersAction = new PublishAvailableUsers($this->event);
        $publishAvailableUsersAction->run();
    }
}
