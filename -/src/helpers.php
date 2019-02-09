<?php

function remote($connectionString)
{
    $remote = \ewma\remoteCall\Remote::getInstance($connectionString);

    if ($remote->isConfigured()) {
        return $remote;
    }
}
