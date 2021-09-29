<?php

namespace App;

use App\Events\Event;

class EventDispatcher
{
    private Application $app;
    private $listeners = [];

    public function __construct(Application $app, array $listeners)
    {
        $this->app = $app;
        $this->listeners = $listeners;
    }

    public function dispatch(Event $event)
    {
        $class = get_class($event);
        if (!isset($this->listeners[$class])) {
            throw new \Exception('Could not dispatch event "' . $class . '" because it was not found.');
        }

        foreach ($this->listeners[$class] as $listenerClass) {
            $listener = $this->app->make($listenerClass, ['event' => $event]);

            // Only dispatch those listeners which handles the correct type of event
            if ($listener->listen() !== $event->type()) {
                continue;
            }

            $returns = $listener->handle();

            // Check if we should continue to call listeners
            // breaks if returning falsy value that is not null
            if ($returns !== null && !$returns) {
                break;
            }
        }
    }
}
