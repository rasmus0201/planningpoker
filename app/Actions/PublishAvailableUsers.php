<?php

namespace App\Actions;

use App\RepositoryFactory;

class PublishAvailableUsers extends Action
{
    public function run()
    {
        $userRepository = RepositoryFactory::createUser();
        $users = array_column(
            $userRepository->getUnconnectedUsers(),
            'username'
        );

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
