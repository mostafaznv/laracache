<?php

namespace Mostafaznv\LaraCache\Actions;

use Mostafaznv\LaraCache\Actions\Support\UpdateDeleteCache;

class UpdateGroupCacheAction extends UpdateDeleteCache
{
    public function run(string $group): void
    {
        $group = $this->group($group);

        foreach ($group as $item) {
            UpdateCacheAction::make($this->console)->run($item);
        }
    }
}
