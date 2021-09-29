<?php

namespace App\Actions;

use App\RepositoryFactory;

class PublishAvailableUsers extends Action
{
    public function run()
    {
        $userRepository = RepositoryFactory::createUser();

        $usernames = $userRepository->getUnconnectedUsers()
            ->pluck('username')
            ->unique()
            ->filter()
            ->all();

        $this->event->broadcast([
            'type' => 'setAvailableUsers',
            'data' => [
                'users' => $usernames,
            ],
        ]);
    }
}
