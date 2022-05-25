<?php

namespace ir\test;

use Blockify\Interfaces\Application;
use Blockify\Models\Api;

class main extends Application
{

    /**
     * @inheritDoc
     */
    public function onRequest(): Api
    {
        return new Api();
    }
}
