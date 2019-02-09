<?php namespace ewma\remoteCall\controllers;

class Main extends \Controller
{
    private $targetConnectionString;

    public function __create()
    {
        if ($direction = $this->data('direction')) {
            if ($targetEnv = $this->parseDirection($direction)) {
                $this->targetConnectionString = $targetEnv->name;
            }
        } else {
            $targetEnvName = $this->data('target'); // todo [app:]env

            if ($targetEnv = \ewma\apps\models\Env::where('name', $targetEnvName)->first()) {
                $this->targetConnectionString = $targetEnv->name;
            }
        }

        if (null === $this->targetConnectionString) {
            $this->lock('not defined target');
        }
    }

    private function parseDirection($direction)
    {
        $exploded = explode('2', $direction);

        if (count($exploded) == 2) {
            $targetEnvShortName = $exploded[1];

            $targetEnv = \ewma\apps\models\Env::where('short_name', $targetEnvShortName)->first();

            if ($targetEnv) {
                return $targetEnv;
            }
        }
    }

    public function call()
    {
        $remote = remote($this->targetConnectionString);

        if ($remote) {
            $path = $this->data('path');
            $data = $this->data('data');

            return $remote->call($path, $data);
        }
    }

    public function asyncCall()
    {
        $remote = remote($this->targetConnectionString);

        if ($remote) {
            $path = $this->data('path');
            $data = $this->data('data');

            return $remote->async($path, $data);
        }
    }
}
