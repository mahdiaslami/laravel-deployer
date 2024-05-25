<?php

namespace MahdiAslami\Deployer;

class Manager
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function createLiara()
    {
        return new Liara($this->config['liara']['app_id'], $this->config['liara']['api_token']);
    }

    public function getEnv()
    {
        $files = $this->config['liara']['env_files'];

        return Env::merge(
            ...array_map(fn($file) => Env::read(base_path($file)), $files)
        );
    }
}