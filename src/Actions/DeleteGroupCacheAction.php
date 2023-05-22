<?php

namespace Mostafaznv\LaraCache\Actions;

use Mostafaznv\LaraCache\Actions\Support\UpdateDeleteCache;

class DeleteGroupCacheAction extends UpdateDeleteCache
{
    public function run(string $group): void
    {
        $group = $this->group($group);

        foreach ($group as $item) {
            DeleteCacheAction::make($this->console)->run($item);
        }
    }
}
