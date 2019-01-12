<?php

namespace ZeusWebsockets;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class ZeusWebsocketsServer implements MessageComponentInterface
{
    protected $clients;
    private $subscriptions;
    private $users;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->subscriptions = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->users[$conn->resourceId] = $conn;
    }

    public function onMessage(ConnectionInterface $conn, $message)
    {
        $data = json_decode($message);
        $messageUserId = $conn->resourceId;

        $messageChannels = [];
        if (isset($data->channel)) {
            $messageChannels = is_array($data->channel) ? $data->channel : [$data->channel];
        }

        switch ($data->action) {
            case 'join':
                if ($messageChannels) {
                    $this->subscriptions[$messageUserId] = $this->subscriptions[$messageUserId] ?? [];
                    $this->subscriptions[$messageUserId] = array_merge($this->subscriptions[$messageUserId], $messageChannels);
                }

                break;
            case 'message':
                $params = [
                    'event' => $data->event,
                    'message' => $data->message
                ];

                if ($messageChannels) {
                    foreach ($this->subscriptions as $userId => $userChannels) {
                        foreach ($userChannels as $userChannel) {
                            if (in_array($userChannel, $messageChannels) && $userId != $messageUserId) {
                                $this->sendTo($this->users[$userId], $params);
                            }
                        }
                    }
                } else {
                    foreach ($this->clients as $client) {
                        if ($conn != $client) {
                            $this->sendTo($client, $params);
                        }
                    }
                }

                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $userId = $conn->resourceId;
        unset($this->users[$userId]);
        unset($this->subscriptions[$userId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $error)
    {
        $conn->close();
    }

    private function sendTo($client, $data)
    {
        $client->send(json_encode($data));
    }
}
