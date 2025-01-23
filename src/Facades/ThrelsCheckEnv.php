<?php

namespace Threls\ThrelsCheckEnv\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Threls\ThrelsCheckEnv\ThrelsCheckEnv
 */
class ThrelsCheckEnv extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Threls\ThrelsCheckEnv\ThrelsCheckEnv::class;
    }
}
