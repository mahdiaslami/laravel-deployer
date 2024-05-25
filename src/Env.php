<?php

namespace MahdiAslami\Deployer;

use Dotenv\Dotenv;

class Env
{
    public static function read(string $path): array
    {
        return static::parse(file_get_contents($path));
    }

    private static function parse(string $content): array
    {
        return Dotenv::parse($content);
    }

    public static function merge(array ...$envs): array
    {
        return array_merge(...$envs);
    }
}
