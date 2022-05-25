<?php

namespace Blockify\Interfaces;

use Blockify\Models\Api;

interface errorApplication
{
    public static function classError(string $className): Api;
    public static function functionError(string $functionName): Api;
    public static function parameterError(array $parameterName): Api;
    public static function parameterCountError(int $count): Api;
    public static function viewError(string $name): String;
}
