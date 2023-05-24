<?php

namespace Mostafaznv\LaraCache\Commands;

use Illuminate\Console\Command;
use Mostafaznv\LaraCache\Actions\DeleteCacheAction;
use Mostafaznv\LaraCache\DTOs\CommandData;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteCacheCommand extends Command
{
    protected $signature = 'laracache:delete {--m|model=*} {--e|entity=*}';
    protected $description = 'Deletes one or multiple cache entities in a single model';

    public function handle(): int
    {
        $models = $this->option('model');
        $entities = $this->option('entity');

        DeleteCacheAction::make($this)->run(
            CommandData::make($models, $entities)
        );


        return SymfonyCommand::SUCCESS;
    }
}
