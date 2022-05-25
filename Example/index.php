<?php

use Blockify\Blockify;

require_once "../vendor/autoload.php";

define("PROJECT", dirname(__FILE__) . "/");

try {
    (new Blockify("main", "main", new \Blockify\App\error()))->start(PROJECT . "app/view/", PROJECT . "app/", "ir\\test\\");
} catch (ReflectionException $e) {
    echo $e->getMessage();
}
