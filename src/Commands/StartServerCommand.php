<?php

namespace ZeusWebsockets\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Http\Controllers\WebsocketController;
use React\EventLoop\Factory;
use React\Socket\SecureServer;
use React\Socket\Server;
use ZeusWebsockets\ZeusWebsocketsServer;

class StartServerCommand extends Command
{
    protected $signature = 'websockets:server';
    protected $description = 'Start websockets server';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $isSsl = env('WEBSOCKETS_SSL', false);
        $port = env('WEBSOCKETS_PORT', 6001);

        if (!$isSsl) {
            // Run with http
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new ZeusWebsocketsServer()
                    )
                ),
                $port
            );

            $this->info('Listening in '.$port.'...');
            $server->run();
        } else {
            // Run with https
            $loop = Factory::create();

            $webSock = new SecureServer(
                new Server('0.0.0.0:'.$port, $loop),
                $loop,
                array(
                    'local_cert' => env('WEBSOCKETS_CERT'),
                    'local_pk' => env('WEBSOCKETS_PK'),
                    'allow_self_signed' => true,
                    'verify_peer' => false
                )
            );

            new IoServer(
                new HttpServer(
                    new WsServer(
                        new ZeusWebsocketsServer()
                    )
                ),
                $webSock
            );

            $this->info('Listening in '.$port.'...');
            $loop->run();
        }
    }
}