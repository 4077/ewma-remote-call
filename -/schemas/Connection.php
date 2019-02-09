<?php namespace ewma\remoteCall\schemas;

class Connection extends \Schema
{
    public $table = 'ewma_remote_call_connections';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('env_id')->default(0);
            $table->string('host')->default('');
            $table->boolean('ssl')->default(false);
            $table->string('key')->default('');
        };
    }
}
