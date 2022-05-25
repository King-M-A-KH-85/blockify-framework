<?php

namespace Blockify\Interfaces;

use Blockify\Models\Api;

abstract class Application
{
    abstract public function onRequest(): Api;
}
