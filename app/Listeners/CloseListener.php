<?php

namespace App\Listeners;

use App\Actions\FinishRound;
use App\Actions\ShowOff;
use App\RepositoryFactory;

class CloseListener extends Listener
{
    public function listen()
    {
        return 'close';
    }

    public function handle()
    {
        $userRepository = RepositoryFactory::createUser();
        $voteRepository = RepositoryFactory::createVote();

        $resourceId = $this->event->getPublisher()->resourceId;
        if ($user = $userRepository->getByResourceId($resourceId)) {
            $userRepository->setUnconnectedById($user['id']);
            $voteRepository->deleteByUserId($user['id']);

            $users = $userRepository->getConnectedUsers();
            $votes = $voteRepository->getVotes();

            $this->event->sendSubscribers([
                'type' => 'leave',
                'data' => [
                    'joined' => array_filter(array_unique(array_column($users, 'username'))),
                    'votes' => array_filter(array_unique(array_column($votes, 'username'))),
                ]
            ]);

            // Check to see if we should make a show off.
            $this->sendShowoffIfAllVoted();

            // Check to see if we should finish the round, and begin a new one.
            $this->finishRound();
        }
    }

    private function sendShowoffIfAllVoted()
    {
        $action = new ShowOff($this->event);
        $action->run();
    }

    private function finishRound()
    {
        $action = new FinishRound($this->event);
        $action->run();
    }
}
