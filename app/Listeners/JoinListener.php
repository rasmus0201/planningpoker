<?php

namespace App\Listeners;

use App\Actions\PublishAvailableUsers;
use App\RepositoryFactory;
use App\Session;

class JoinListener extends Listener
{
    public function listen()
    {
        return 'open';
    }

    public function handle()
    {
        $session = Session::fromRequest($this->event->request);

        $clientId = $session->get('clientId');
        $gamepin = $session->get('gamepin');

        if (!$clientId || !$gamepin) {
            return;
        }

        // Check gamepin
        $gameRepository = RepositoryFactory::createGame();

        if (!$game = $gameRepository->getByPin($gamepin)) {
            return;
        }

        $connectionRepository = RepositoryFactory::createConnection();

        // Create connection
        $connectionRepository->create(
            $this->event->getPublisher()->resourceId,
            $clientId,
            $game->id
        );

        $publishAvailableUsersAction = new PublishAvailableUsers($this->event);
        $publishAvailableUsersAction->run();

        $this->event->sendPublisher([
            'type' => 'setGame',
            'data' => [
                'id' => $game->id,
                'state' => $game->state,
            ]
        ]);
    }
}
