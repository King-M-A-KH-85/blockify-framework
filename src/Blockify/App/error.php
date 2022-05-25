<?php

namespace Blockify\App;

use Blockify\Interfaces\Application;
use Blockify\Interfaces\errorApplication;
use Blockify\Models\Api;

class error implements errorApplication
{

    public static function classError(string $className): Api
    {
        return new Api(false, "$className class notFound");
    }

    public static function functionError(string $functionName): Api
    {
        return new Api(false, "$functionName notFound");
    }

    public static function parameterError(array $parameterName): Api
    {
        return new Api(false, "please enter parameters", $parameterName);
    }

    public static function parameterCountError(int $count): Api
    {
        return new Api(false, "parameter count is $count");
    }

    public static function viewError(string $name): string
    {
        return "error";
    }
}
