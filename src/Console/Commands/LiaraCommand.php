<?php

namespace MahdiAslami\Deployer\Console\Commands;

use MahdiAslami\Deployer\Console\Utilities\CanWriteMute;
use MahdiAslami\Deployer\Manager;
use Illuminate\Console\Command;

class LiaraCommand extends Command
{
    use CanWriteMute;

    protected $signature = 'deploy:liara';

    protected $description = 'Deploy project to liara app';

    protected $outputs = [];

    private $manager;

    public function handle(Manager $manager): int
    {
        $this->manager = $manager;

        $this->envTask();

        $this->deployTask();

        return static::SUCCESS;
    }

    private function envTask()
    {
        $liara = $this->manager->createLiara();
        $env = $this->manager->getEnv();

        $this->preserveCursorPosition(function () use ($liara, $env) {
            $this->line('- Updating environment variables');
            $liara->setEnv($env);
        });

        $this->info('• Environment variables updated');
    }

    private function deployTask()
    {
        $liara = $this->manager->createLiara();

        $this->line('- Deploying on Liara');
        $this->line('');

        $liara->deploy(
            fn($output) => empty ($output) ?: $this->writeOutputs($output)
        );

        $this->newLine(6);

        $this->info('• Deployed on Liara');
    }

    public function writeOutputs(string $output): void
    {
        $this->outputs = array_merge($this->outputs, explode("\n", trim($output)));
        $this->outputs = array_slice($this->outputs, count($this->outputs) - 6, 5);

        // Clear to the line end
        $this->output->write("\033[J");
        $this->preserveCursorPosition(function () {
            foreach ($this->outputs as $output) $this->mute($output);
        });
    }
}
