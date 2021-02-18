<?php

namespace App\Events;

use Exception;
use Ratchet\ConnectionInterface;

abstract class Event
{
    const MAX_NESTING = 2;
    private static $nestingLevel = 0;

    private $eventDispatcher;
    private $subscribers;
    private $publisher;

    private $type;

    public $data;

    public function __construct($type, $data = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->subscribers = new \SplObjectStorage();
    }

    public function type()
    {
        $type = ($this->data['type'] ?? $this->type);

        // Sub-typing
        if ($type !== $this->type) {
            return $this->type.':'.$type;
        }

        return $type;
    }

    public function setSubscribers(\SplObjectStorage $connections)
    {
        $this->subscribers = $connections;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function setPublisher(ConnectionInterface $connection)
    {
        $this->publisher = $connection;
    }

    public function getPublisher()
    {
        return $this->publisher;
    }

    public function sendSubscribers(array $message)
    {
        foreach ($this->subscribers as $subscriber) {
            if ($this->isPublisher($subscriber)) {
                continue;
            }

            $subscriber->send(json_encode($message));
        }
    }

    public function sendPublisher(array $message)
    {
        $this->getPublisher()->send(json_encode($message));
    }

    public function isPublisher($subscriber)
    {
        return $this->publisher->resourceId === $subscriber->resourceId;
    }

    public function setEventDispatcher($dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function dispatch(Event $event)
    {
        if (static::$nestingLevel == self::MAX_NESTING) {
            throw new Exception('Too many levels of nesting!');
        }

        if ($event->type() === $this->type()) {
            throw new Exception('Recursion error prevented!');
        }

        $event->setPublisher($this->getPublisher());
        $event->setSubscribers($this->getSubscribers());
        $event->setEventDispatcher(clone $this->eventDispatcher);

        static::$nestingLevel += 1;
        $this->eventDispatcher->dispatch($event);
    }
}
