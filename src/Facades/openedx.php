<?php

namespace ngunyimacharia\openedx\Facades;

use Illuminate\Support\Facades\Facade;

class Openedx extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'openedx';
    }
}
