<?php

namespace MahdiAslami\Deployer;

use Ds\Queue;
use Symfony\Component\Process\Process;

class Liara
{
    private string $appId;

    private string $apiToken;

    private Process $process;

    public function __construct(string $appId, string $apiToken)
    {
        $this->appId = $appId;
        $this->apiToken = $apiToken;
    }

    public function envList(): array
    {
        $output = $this->runCommand(['env', 'list', '--output=json']);

        return array_merge(
            ...array_map(
                fn($entry) => [$entry['key'] => $entry['value']],
                json_decode($output, true)
            )
        );
    }

    public function setEnv(array $env): string
    {
        $env = array_diff_assoc($env, $this->envList());

        $list = [];
        foreach ($env as $key => $value) {
            $list[] = "$key=$value";
        }

        return $this->runCommand(['env', 'set', ...$list, '--force']);
    }

    public function unsetEnv(array $keys): string
    {
        return $this->runCommand(['env', 'unset', ...$keys, '--force']);
    }

    public function deploy(callable $callback)
    {
        $process = $this->startCommand(['deploy', '--no-app-logs']);

        while ($process->isRunning()) {
            $output = trim($process->getIncrementalOutput());

            if (!empty($output)) {
                call_user_func($callback, $output);
            }

            usleep(10000);
        }

        if ($process->getExitCode() !== 0) {
            call_user_func($callback, $process->getErrorOutput());
        }
    }

    private function runCommand(array $command): string
    {
        $process = new Process(['liara', ...$command, '--api-token=' . $this->apiToken, '--app=' . $this->appId]);

        $process->run();

        return $process->getOutput();
    }

    private function startCommand(array $command): Process
    {
        $process = new Process(['liara', ...$command, '--api-token=' . $this->apiToken, '--app=' . $this->appId]);

        $process->start();

        return $process;
    }
}
