<?php

namespace App\Actions;

use App\RepositoryFactory;

class ExcludeMidGameJoin extends Action
{
    public function run()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if (!$clientId) {
            return;
        }

        $userRepository = RepositoryFactory::createUser();
        $voteRepository = RepositoryFactory::createVote();

        $votesCount = $voteRepository->countVotesExcludeByClientId($clientId);

        // If there is already votes in,
        // Then send a notification saying that you are waiting for results
        if ($votesCount > 0) {
            // Set exclude flag for user
            $userRepository->setExcludedByClientId($clientId, 1);

            $this->event->sendSubscribers([
                'type' => 'excluded',
                'data' => [
                    'username' => $this->event->data['data']['username']
                ]
            ]);

            $this->event->sendPublisher([
                'type' => 'midgame_join',
                'data' => []
            ]);
        }
    }
}
