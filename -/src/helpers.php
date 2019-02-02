<?php

function remote($serverName)
{
    return \ewma\remoteCall\Remote::getInstance($serverName);
}
