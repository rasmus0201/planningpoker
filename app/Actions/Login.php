<?php

namespace App\Actions;

use App\RepositoryFactory;

class Login extends Action
{
    public function run()
    {
        $userRepository = RepositoryFactory::createUser();
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if ($user = $userRepository->getByClientId($clientId)) {
            $userRepository->setConnectedById(
                $user['id'],
                $this->event->getPublisher()->resourceId
            );

            $users = $userRepository->getConnectedUsers();
            $votes = $userRepository->getUsersThatVoted();

            $this->event->sendPublisher([
                'type' => 'login',
                'data' => [
                    'session' => [
                        'clientId' => $clientId,
                        'username' => $user['username'],
                        'advanced' => (bool) $user['is_advanced'],
                        'midgame_join' => (bool) $user['is_excluded'],
                        'auth' => true,
                    ],
                    'joined' => array_filter(array_unique(array_column($users, 'username'))),
                    'votes' => array_filter(array_unique(array_column($votes, 'username'))),
                ]
            ]);

            $this->event->sendSubscribers([
                'type' => 'join',
                'data' => [
                    'username' => $user['username'],
                ]
            ]);

            $this->sendShowoffIfAllVoted();
        }
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);
        $action->run();
    }
}
