<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api;

class ApiRegistry
{
    /** @var array<string, Api> */
    private array $apis = [];

    public function addApi(Api $api, string $alias): void
    {
        $api->setAlias($alias);

        $this->apis[$alias] = $api;
    }

    public function getApi(string $alias): ?Api
    {
        return $this->apis[$alias] ?? null;
    }

    /**
     * @return string[]
     */
    public function getAliasList(): array
    {
        return array_keys($this->apis);
    }
}
