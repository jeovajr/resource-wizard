<?php

namespace ResourceWizard\Services;

class ResourceWizard
{
    private array $resources;

    public function __construct()
    {
        $this->resources = [];

        if (is_array(config('resources'))) {
            foreach (config('resources') as $resource) {
                $this->resources[] = $resource;
            }
        }
    }

    public function getResources(): array
    {
        return $this->resources;
    }
}
