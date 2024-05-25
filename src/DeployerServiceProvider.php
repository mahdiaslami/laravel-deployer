<?php

namespace MahdiAslami\Deployer;

use Illuminate\Support\ServiceProvider;
use MahdiAslami\Deployer\Console\Commands\CopyDiskCommand;
use MahdiAslami\Deployer\Console\Commands\LiaraCommand;

class DeployerServiceProvider extends ServiceProvider
{
    public function register()
    {    
        $this->app->bind(Manager::class, function () {
            return new Manager(
                config('deployer.deployment')
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/deployer.php' => config_path('deployer.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                LiaraCommand::class,
                CopyDiskCommand::class
            ]);
        }    
    }
}