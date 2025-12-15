<?php

namespace Mostafaznv\LaraCache\Actions\Support;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Mostafaznv\LaraCache\DTOs\CommandData;
use Mostafaznv\LaraCache\Exceptions\CacheGroupNotExist;
use Mostafaznv\LaraCache\Exceptions\CacheGroupValueIsNotValid;


abstract class UpdateDeleteCache
{
    public function __construct(protected ?Command $console) {}

    public static function make(?Command $console = null): self
    {
        return new static($console);
    }


    protected function title(string $string): string
    {
        return Str::title(
            Str::slug(
                title: Str::replace(['.', '-', '_'], ' ', $string),
                separator: ' '
            )
        );
    }

    /**
     * @param string $group
     * @return CommandData[]
     */
    protected function group(string $group): array
    {
        $groupName = $group;
        $group = config("laracache.groups.$group");

        if (is_null($group)) {
            throw CacheGroupNotExist::make($groupName);
        }

        if (is_array($group)) {
            $data = [];

            foreach ($group as $item) {
                $data[] = $this->makeCommandDataFromGroupItem($item, $groupName);
            }

            return $data;
        }
        else {
            throw CacheGroupValueIsNotValid::make($groupName);
        }
    }

    private function makeCommandDataFromGroupItem(mixed $item, string $groupName): CommandData
    {
        if (isset($item['model']) and is_string($item['model']) and isset($item['entities'])) {
            return CommandData::make(
                models: [$item['model']],
                entities: $item['entities']
            );
        }
        else {
            throw CacheGroupValueIsNotValid::make($groupName);
        }
    }
}
