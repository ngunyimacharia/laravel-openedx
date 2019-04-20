<?php

namespace ngunyimacharia\laravel-openedx\Facades;

use Illuminate\Support\Facades\Facade;

class laravel-openedx extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-openedx';
    }
}
