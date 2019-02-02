<?php namespace ewma\remoteCall\controllers;

class Router extends \Controller implements \ewma\Interfaces\RouterInterface
{
    public function getResponse()
    {
        $settings = dataSets()->get('ewma/remoteCall:');

        $this->route($settings['route'])->to('handler:handle');

        return $this->routeResponse();
    }
}
