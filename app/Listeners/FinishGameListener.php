<?php

namespace App\Listeners;

use App\Models\Game;
use App\Models\User;
use App\RepositoryFactory;

class FinishGameListener extends Listener
{
    public function listen()
    {
        return 'message:finishGame';
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

        if ($user->type !== User::TYPE_GAMEMASTER) {
            return;
        }

        $gameRepository = RepositoryFactory::createGame();
        $gameRepository->setStateById($user->game->id, Game::STATE_FINISHED);

        $this->event->broadcast([
            'type' => 'setGame',
            'data' => [
                'state' => Game::STATE_FINISHED,
            ],
        ]);
    }
}
