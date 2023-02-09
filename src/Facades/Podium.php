<?php

namespace Jaulz\Podium\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jaulz\Podium\Podium
 */
class Podium extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Jaulz\Podium\Podium::class;
    }
}