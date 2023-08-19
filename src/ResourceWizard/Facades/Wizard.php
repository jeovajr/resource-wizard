<?php

declare(strict_types=1);

namespace Jeovajr\ResourceWizard\Facades;

use Illuminate\Support\Facades\Facade;

final class Wizard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'wizard';
    }
}
