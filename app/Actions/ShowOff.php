<?php

namespace App\Actions;

use App\RepositoryFactory;

class ShowOff extends Action
{
    public function run()
    {
        $userRepository = RepositoryFactory::createUser();
        $voteRepository = RepositoryFactory::createVote();

        $countConnectedUsers = $userRepository->countVotingUsers();
        $votes = $voteRepository->getVotes();

        if ($countConnectedUsers !== count($votes)) {
            return;
        }

        try {
            $this->event->sendSubscribers([
                'type' => 'showoff',
                'data' => $votes
            ]);
        } catch (\Throwable $th) {
        }

        try {
            $this->event->sendPublisher([
                'type' => 'showoff',
                'data' => $votes
            ]);
        } catch (\Throwable $th) {
        }
    }
}
