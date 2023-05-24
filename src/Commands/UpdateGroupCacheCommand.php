<?php

namespace Mostafaznv\LaraCache\Commands;

use Illuminate\Console\Command;
use Mostafaznv\LaraCache\Actions\UpdateGroupCacheAction;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class UpdateGroupCacheCommand extends Command
{
    protected $signature = 'laracache:update-group {group}';
    protected $description = 'Updates all models and entities in a group';

    public function handle(): int
    {
        UpdateGroupCacheAction::make($this)->run(
            $this->argument('group')
        );

        return SymfonyCommand::SUCCESS;
    }
}
