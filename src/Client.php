<?php

namespace ZeusWebsockets;

class Client
{
    const DEFAULT_PROTOCOL = 'ws';
    const DEFAULT_PORT = 'ws';
    const DEFAULT_HOST = '127.0.0.1';

    private $connection;
    private $url;

    public function __construct(string $url)
    {
        $urlParsed = parse_url($url);

        $protocol = $this->getProtocol($urlParsed['scheme'] ?? self::DEFAULT_PROTOCOL);
        $host = gethostbyname($urlParsed['host'] ?? self::DEFAULT_HOST);
        $port = $urlParsed['port'] ?? self::DEFAULT_PORT;

        $this->url = $protocol.'://'.$host.':'.$port;
    }

    public function onConnect($callback)
    {
        $loop = \React\EventLoop\Factory::create();
        $reactConnector = new \React\Socket\Connector($loop, [
            'dns' => false,
            'tls' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            )
        ]);

        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);

        $that = $this;
        $connector($this->url)->then(function ($connection) use ($callback, $that) {
            $this->connection = $connection;
            $callback($that);
            $this->connection->close();
        }, function ($error) {
            throw new Exception($error->getMessage());
        });

        $loop->run();
    }

    public function emit(string $event, $message, $channel = null)
    {
        $params = [
            'action' => 'message',
            'event' => $event,
            'message' => $message
        ];

        if ($channel) {
            $params['channel'] = $channel;
        }

        $this->connection->send(json_encode($params));
    }

    public function close()
    {
        $this->connection->close();
    }

    private function getProtocol(string $scheme = null)
    {
        if ($scheme == 'http') {
            return 'ws';
        } else if ($scheme == 'https') {
            return 'wss';
        }
        
        return $scheme;
    }

}