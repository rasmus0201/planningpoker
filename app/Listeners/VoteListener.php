<?php

namespace App\Listeners;

use App\Actions\ShowOff;
use App\RepositoryFactory;

class VoteListener extends Listener
{
    public function listen()
    {
        return 'message:vote';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';
        $vote = $this->event->data['data']['vote'] ?? '0';

        if (!$clientId) {
            $this->sendResetVote(null, 'No clientId');

            return;
        }

        if (strlen($vote) > 12) {
            $this->sendResetVote(null, 'No vote over 12 chars');

            return;
        }

        $userRepository = RepositoryFactory::createUser();

        if (!$user = $userRepository->getByClientId($clientId)) {
            $this->sendResetVote(null, 'No user connected with clientId');

            return;
        }

        $user->load(['game', 'game.latestRound']);
        $round = $user->game->latestRound ?? null;
        if (!$round) {
            $this->sendResetVote(null, 'No round for user');

            return;
        }

        $voteRepository = RepositoryFactory::createVote();
        if ($voteModel = $voteRepository->getUserVote($round->id, $user->id)) {
            $this->sendResetVote($voteModel->vote, 'User already voted');

            return;
        }

        $voteRepository->insertVote(
            $round->id,
            $user->id,
            $vote
        );

        $this->event->broadcast([
            'type' => 'setVotingUsers',
            'data' => [
                'users' => $userRepository->getVoted($round->id)->pluck('username'),
            ]
        ]);

        $this->event->data['system'] = [
            'round' => $round,
        ];

        $this->sendShowoffIfAllVoted();
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);

        $action->run();
    }

    private function sendResetVote($vote, $reason)
    {
        $this->event->sendPublisher([
            'type' => 'setVote',
            'data' => [
                'vote' => $vote,
                'reason' => $reason,
            ],
        ]);
    }
}
