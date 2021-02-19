<?php

namespace App\Listeners;

use App\Actions\Login;
use App\Actions\PublishAvailableUsers;

class ConnectListener extends Listener
{
    public function listen()
    {
        return 'message:connect';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if ($clientId) {
            $loginAction = new Login($this->event);
            $loginAction->run();
        }

        $publishAvailableUsersAction = new PublishAvailableUsers($this->event);
        $publishAvailableUsersAction->run();
    }
}
