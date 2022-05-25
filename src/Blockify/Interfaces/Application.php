<?php

namespace Blockify\Interfaces;

use Blockify\Models\Api;

/**
 * @author king m a kh
 * @note for apis
 * @link https://blockifyApi.ir/doc/blockify/interfaces/application
 * @version 1.0
 */
abstract class Application
{
    /**
     * @note
     * @link https://blockifyApi.ir/doc/blockify/interfaces/application/onRequest
     * @version 1.0
     */
    abstract public function onRequest(): Api;
}
