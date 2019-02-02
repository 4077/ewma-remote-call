<?php namespace ewma\remoteCall\controllers;

class Main extends \Controller
{
    public function call()
    {
        $server = remote($this->data('server'));

        if ($server) {
            $path = $this->data('path');
            $data = $this->data('data');

            return $server->call($path, $data);
        }
    }

    public function asyncCall()
    {
        $server = remote($this->data('server'));

        if ($server) {
            $path = $this->data('path');
            $data = $this->data('data');

            return $server->async($path, $data);
        }
    }
}
