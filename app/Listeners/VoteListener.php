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
        $voteId = (int) $this->event->data['data']['vote'] ?? 0;

        if (!$clientId) {
            $this->sendResetVote();

            return;
        }

        if ($voteId < 0 || $voteId > 12) {
            $this->sendResetVote();

            return;
        }

        $userRepository = RepositoryFactory::createUser();

        if (!$user = $userRepository->getByClientId($clientId)) {
            $this->sendResetVote();

            return;
        }

        $voteRepository = RepositoryFactory::createVote();

        $voteRepository->insertVote(
            $user['id'],
            $voteId
        );

        $this->event->sendSubscribers([
            'type' => 'vote',
            'data' => [
                'username' => $user['username'],
            ]
        ]);

        $this->sendShowoffIfAllVoted();
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);

        $action->run();
    }

    private function sendResetVote()
    {
        $this->event->sendPublisher([
            'type' => 'reset_vote',
            'data' => []
        ]);
    }
}
