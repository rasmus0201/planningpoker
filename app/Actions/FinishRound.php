<?php

namespace App\Actions;

use App\RepositoryFactory;

class FinishRound extends Action
{
    public function run()
    {
        $userRepository = RepositoryFactory::createUser();
        $voteRepository = RepositoryFactory::createVote();

        $counts = $userRepository->countAdvancedUsers();
        if ($counts['user_count'] !== $counts['advanced_count']) {
            return;
        }

        $voteRepository->deleteAll();
        $userRepository->resetAdvancedAll();
        $userRepository->resetExcludedAll();

        try {
            $this->event->sendSubscribers([
                'type' => 'finish',
                'data' => [
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        } catch (\Throwable $th) {
        }

        try {
            $this->event->sendPublisher([
                'type' => 'finish',
                'data' => [
                    'message' => 'Round finished, all moved to next round'
                ]
            ]);
        } catch (\Throwable $th) {
        }
    }
}
