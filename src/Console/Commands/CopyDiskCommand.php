<?php

namespace MahdiAslami\Deployer\Console\Commands;

use Illuminate\Console\Command;
use League\Flysystem\FileAttributes;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\StorageAttributes;
use League\Flysystem\DirectoryAttributes;

class CopyDiskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disk:copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy local one disk to another';

    protected $sourceDisk, $destinationDisk, $bar;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourceDisk = $this->ask('Enter Source Disk:');
        $destinationDisk = $this->ask('Enter Destination Disk:');
        $overwrite = $this->confirm('Do you want files be overwritten?');

        $this->sourceDisk = Storage::disk($sourceDisk);
        $this->destinationDisk = Storage::disk($destinationDisk);

        $this->bar = $this->output->createProgressBar(count($this->sourceDisk->listContents('', true)->toArray()) - 1);
        $this->bar->start();

        $this->store(new DirectoryAttributes(''), $overwrite);

        $this->bar->finish();
        $this->newLine(2);

        return Command::SUCCESS;
    }

    private function store(StorageAttributes $attributes, bool $overwrite): void
    {
        if ($attributes instanceof DirectoryAttributes) {
            if ($attributes->path()) {
                if ($makeDirectoryResult = $this->destinationDisk->makeDirectory($attributes->path())) $this->bar->advance();
            } else {
                $makeDirectoryResult = true;
            }
            if ($makeDirectoryResult) {
                $contents = $this->sourceDisk->listContents($attributes->path(), false)->toArray();
                foreach ($contents as $content) {
                    $this->store($content, $overwrite);
                }
            } else {
                $this->error("Unable to make '{$attributes->path()}' directory.");
            }
        } elseif ($attributes instanceof FileAttributes) {
            if (!$overwrite and $this->destinationDisk->exists($attributes->path())) {
                $this->bar->advance();
                return;
            }
            if ($this->destinationDisk->put($attributes->path(), $this->sourceDisk->get($attributes->path()))) {
                $this->bar->advance();
            } else {
                $this->error("Unable to put '{$attributes->path()}' file.");
            }
        }
    }
}
