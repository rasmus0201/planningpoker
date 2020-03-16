<?php

namespace App;

use App\Events\CloseEvent;
use App\Events\ErrorEvent;
use App\Events\MessageEvent;
use App\Events\OpenEvent;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Socket implements MessageComponentInterface
{
    private $clients;
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        try {
            $event = new OpenEvent();
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher(clone $this->eventDispatcher);

            $this->eventDispatcher->dispatch($event);
        } catch (Exception $e) {
            $this->sendError(
                $conn,
                [
                    $e->getMessage(),
                    explode("\n", $e->getTraceAsString())
                ]
            );
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $json = json_decode($msg, true);

        try {
            $event = new MessageEvent($json);
            $event->setSubscribers($this->clients);
            $event->setPublisher($from);
            $event->setEventDispatcher(clone $this->eventDispatcher);

            $this->eventDispatcher->dispatch($event);
        } catch (Exception $e) {
            $this->sendError(
                $from,
                [
                    $e->getMessage(),
                    explode("\n", $e->getTraceAsString())
                ]
            );
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        try {
            $event = new CloseEvent();
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher(clone $this->eventDispatcher);

            $this->eventDispatcher->dispatch($event);
        } catch (Exception $e) {
            // Nothing to send error to.
        }
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        try {
            $event = new CloseEvent($e);
            $event->setSubscribers($this->clients);
            $event->setPublisher($conn);
            $event->setEventDispatcher(clone $this->eventDispatcher);
        } catch (Exception $eventException) {
            $this->sendError(
                $conn,
                [
                    $e->getMessage(),
                    explode("\n", $e->getTraceAsString())
                ]
            );
        }
    }

    private function sendError($connection, $error = '')
    {
        $connection->send(json_encode([
            'type' => 'error',
            'message' => 'Something went wrong.',
            'data' => [
                'error' => $error
            ]
        ]));
    }
}
