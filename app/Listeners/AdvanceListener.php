<?php

namespace App\Listeners;

use App\Actions\FinishRound;
use App\RepositoryFactory;

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

        $userRepository = RepositoryFactory::createUser();

        if (!$user = $userRepository->getByClientId($clientId)) {
            return;
        }

        $userRepository->setAdvancedById(
            $user['id'],
            1
        );

        $action = new FinishRound($this->event);
        $action->run();
    }
}
