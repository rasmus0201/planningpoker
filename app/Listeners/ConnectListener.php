<?php

namespace App\Listeners;

use App\RepositoryFactory;

class ConnectListener extends Listener
{
    public function listen()
    {
        return 'open';
    }

    public function handle()
    {
        $ids = [];
        foreach ($this->event->getSubscribers() as $subscriber) {
            $ids[] = $subscriber->resourceId;
        }

        $connectionRepository = RepositoryFactory::createConnection();
        $connectionRepository->syncByIds($ids);
    }
}
