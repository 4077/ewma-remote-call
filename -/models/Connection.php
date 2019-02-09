<?php namespace ewma\remoteCall\models;

class Connection extends \Model
{
    public $table = 'ewma_remote_call_connections';

    public function env()
    {
        return $this->belongsTo(\ewma\apps\models\Env::class);
    }
}
