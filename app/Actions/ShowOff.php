<?php

namespace App\Actions;

use App\Models\Game;
use App\RepositoryFactory;

class ShowOff extends Action
{
    public function run()
    {
        $round = $this->event->data['system']['round'] ?? null;

        if (!$round) {
            return;
        }

        $userRepository = RepositoryFactory::createUser();
        $voteRepository = RepositoryFactory::createVote();

        $countUsersVoted = $userRepository->countVoted($round->id);
        $countConnectedUsers = $userRepository->countConnectedUsers($round->game_id);

        if ($countUsersVoted !== $countConnectedUsers) {
            return;
        }

        $gameRepository = RepositoryFactory::createGame();
        $gameRepository->setStateById($round->game_id, Game::STATE_SHOWOFF);

        $votes = $voteRepository->getVotes($round->id)->sortBy('user.username')
            ->map(function ($item) {
                return [
                    'value' => $item->vote,
                    'username' => $item->user->username,
                ];
            })
            ->values();

        try {
            $this->event->broadcast([
                'type' => 'setGame',
                'data' => [
                    'state' => Game::STATE_SHOWOFF,
                ],
            ]);

            $this->event->broadcast([
                'type' => 'setVotes',
                'data' => [
                    'votes' => $votes,
                ],
            ]);
        } catch (\Throwable $th) {
        }
    }
}
