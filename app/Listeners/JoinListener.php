<?php

namespace App\Listeners;

use App\Actions\ExcludeMidGameJoin;
use App\Actions\Login;
use App\Actions\PublishAvailableUsers;
use App\RepositoryFactory;

class JoinListener extends Listener
{
    public function listen()
    {
        return 'message:join';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';
        $username = $this->event->data['data']['username'] ?? '';

        if (!$clientId || !$username) {
            return;
        }

        $userRepository = RepositoryFactory::createUser();

        if (!$user = $userRepository->getByUsername($username)) {
            return;
        }

        // Reset user info
        $userRepository->setClientIdById($user['id'], $clientId);
        $userRepository->setAdvancedById($user['id'], 0);
        $userRepository->setExcludedById($user['id'], 0);

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
