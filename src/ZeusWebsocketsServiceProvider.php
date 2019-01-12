<?php

namespace ZeusWebsockets;

use Illuminate\Support\ServiceProvider;

class ZeusWebsocketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            Commands\StartServerCommand::class
        ]);
    }

    public function register()
    {

    }
}