<?php

namespace App\Events;

use App\EventDispatcher;
use App\Log;
use Exception;
use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

abstract class Event
{
    const MAX_NESTING = 2;
    private static int $nestingLevel = 0;

    private EventDispatcher $eventDispatcher;
    private SplObjectStorage $subscribers;
    private ConnectionInterface $publisher;
    private ?string $publisherChannel;

    private string $type;

    public Request $request;
    public array $data;

    public function __construct(string $type, Request $request, array $body = [])
    {
        $this->type = $type;
        $this->request = $request;
        $this->data = $body;
        $this->publisherChannel = null;
        $this->subscribers = new SplObjectStorage();
    }

    public function type(): string
    {
        $type = ($this->data['type'] ?? $this->type);

        // Sub-typing
        if ($type !== $this->type) {
            return $this->type.':'.$type;
        }

        return $type;
    }

    public function setSubscribers(SplObjectStorage $connections): void
    {
        $this->subscribers = $connections;
    }

    public function getSubscribers(): SplObjectStorage
    {
        return $this->subscribers;
    }

    public function setPublisher(ConnectionInterface $connection): void
    {
        $this->publisher = $connection;
    }

    public function getPublisher(): ConnectionInterface
    {
        return $this->publisher;
    }

    public function sendSubscribers(array $message, $channel = null): void
    {
        foreach ($this->subscribers as $subscriber) {
            if ($this->isPublisher($subscriber)) {
                continue;
            }
            if (!$channel) {
                $subscriber->send(json_encode($message));

                continue;
            }

            if ($this->getChannelFromInfo($this->subscribers->getInfo()) === $channel) {
                $subscriber->send(json_encode($message));
            }
        }
    }

    public function broadcast(array $message, $channel = null): void
    {
        foreach ($this->subscribers as $subscriber) {
            if (!$channel) {
                $subscriber->send(json_encode($message));
            }

            if ($this->getChannelFromInfo($this->subscribers->getInfo()) === $channel) {
                $subscriber->send(json_encode($message));
            }
        }
    }

    public function sendPublisher(array $message): void
    {
        $this->getPublisher()->send(json_encode($message));
    }

    public function getPublisherChannel(): string
    {
        if ($this->publisherChannel === null) {
            $publisherResourceId = $this->publisher->resourceId;
            foreach ($this->channels as $id => $subscribers) {
                if (!isset($subscribers[$publisherResourceId])) {
                    continue;
                }

                $this->publisherChannel = $id;
                break;
            }

            // If no publisher is subcriping to no channel, set it as an empty string.
            if ($this->publisherChannel === null) {
                $this->publisherChannel = '';
            }
        }

        return $this->publisherChannel;
    }

    public function isPublisher($subscriber): bool
    {
        return $this->publisher->resourceId === $subscriber->resourceId;
    }

    public function setEventDispatcher(EventDispatcher $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function dispatch(Event $event): void
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

    private function getChannelFromInfo(array $data): ?string
    {
        return $data['channel'] ?? null;
    }
}
