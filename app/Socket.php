<?php

namespace App;

use App\Events\CloseEvent;
use App\Events\MessageEvent;
use App\Events\OpenEvent;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class Socket implements MessageComponentInterface
{
    private SplObjectStorage $clients;
    private Application $app;
    private EventDispatcher $eventDispatcher;
    private Log $logger;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->clients = new SplObjectStorage();
        $this->eventDispatcher = $this->app->make(EventDispatcher::class);
        $this->logger = $this->app->make(Log::class);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn, [
            'channel' => $this->getConnectionChannel($conn)
        ]);

        try {
            $event = new OpenEvent($conn->httpRequest);
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher($this->eventDispatcher);

            $this->eventDispatcher->dispatch($event);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        try {
            $json = json_decode($msg, true);

            $event = new MessageEvent($conn->httpRequest, $json);
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher($this->eventDispatcher);

            $this->eventDispatcher->dispatch($event);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        try {
            $event = new CloseEvent($conn->httpRequest);
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher($this->eventDispatcher);

            $this->eventDispatcher->dispatch($event);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        try {
            $event = new CloseEvent($conn->httpRequest, $e);
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher($this->eventDispatcher);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    private function getConnectionChannel(ConnectionInterface $conn): ?string
    {
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);
        if (!isset($query['channel'])) {
            return null;
        }

        return $query['channel'];
    }
}
