<?php

namespace Mostafaznv\LaraCache\Commands;

use Illuminate\Console\Command;
use Mostafaznv\LaraCache\Actions\UpdateCacheAction;
use Mostafaznv\LaraCache\DTOs\CommandData;
use Symfony\Component\Console\Command\Command as SymfonyCommand;


class UpdateCacheCommand extends Command
{
    protected $signature   = 'laracache:update {--m|model=*} {--e|entity=*}';
    protected $description = 'Updates one or multiple cache entities in a single model';


    public function handle(): int
    {
        $models = $this->option('model');
        $entities = $this->option('entity');

        UpdateCacheAction::make($this)->run(
            CommandData::make($models, $entities)
        );


        return SymfonyCommand::SUCCESS;
    }
}
