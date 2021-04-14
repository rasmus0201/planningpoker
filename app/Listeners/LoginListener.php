<?php

namespace App\Listeners;

use App\Actions\PublishAvailableUsers;
use App\Models\Game;
use App\RepositoryFactory;

class LoginListener extends Listener
{
    public function listen()
    {
        return 'message:login';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';
        $username = $this->event->data['data']['username'] ?? '';

        if (!$clientId || !$username) {
            return;
        }

        $connectionRepository = RepositoryFactory::createConnection();
        if (!$connection = $connectionRepository->getByClientId($clientId)) {
            return;
        }

        $userRepository = RepositoryFactory::createUser();
        if (!$user = $userRepository->getByUsername($username)) {
            return;
        }

        $connection->update([
            'user_id' => $user->id,
        ]);

        $userRepository->cleanPreviousByClientId($connection->client_id, $user->id);

        $user->update([
            'connection_id' => $connection->id,
            'client_id' => $connection->client_id,
        ]);

        $publishAvailableUsersAction = new PublishAvailableUsers($this->event);
        $publishAvailableUsersAction->run();

        $this->event->sendPublisher([
            'type' => 'setSessionData',
            'data' => [
                'auth' => true,
                'userType' => $user->type,
            ],
        ]);

        $connection->load(['game', 'game.latestRound']);
        $this->event->broadcast([
            'type' => 'setVotingUsers',
            'data' => [
                'users' => $userRepository->getVoted($connection->game->latestRound->id)->pluck('username'),
            ]
        ]);

        $this->event->broadcast([
            'type' => 'setAuthenticatedPlayers',
            'data' => [
                'users' => $userRepository->getAuthenticatedPlayers($connection->game_id)->pluck('username'),
            ],
        ]);

        if ($connection->game->state == Game::STATE_SHOWOFF) {
            $voteRepository = RepositoryFactory::createVote();
            $votes = $voteRepository->getVotes($connection->game->latestRound->id)->sortBy('user.username')
                ->map(function ($item) {
                    return [
                        'value' => $item->vote,
                        'username' => $item->user->username,
                    ];
                })
                ->values();

            $this->event->broadcast([
                'type' => 'setVotes',
                'data' => [
                    'votes' => $votes
                ],
            ]);
        }
    }
}
