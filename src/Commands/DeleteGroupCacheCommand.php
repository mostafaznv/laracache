<?php

namespace Mostafaznv\LaraCache\Commands;

use Illuminate\Console\Command;
use Mostafaznv\LaraCache\Actions\DeleteGroupCacheAction;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteGroupCacheCommand extends Command
{
    protected $signature = 'laracache:delete-group {group}';
    protected $description = 'Deletes all models and entities in a group';

    public function handle(): int
    {
        DeleteGroupCacheAction::make($this)->run(
            $this->argument('group')
        );

        return SymfonyCommand::SUCCESS;
    }
}
