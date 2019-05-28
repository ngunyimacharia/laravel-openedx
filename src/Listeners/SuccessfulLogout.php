<?php

namespace ngunyimacharia\openedx\Listeners;
use ngunyimacharia\openedx\Controllers\EdxLogoutController as LogoutController;

class SuccessfulLogout
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $controller = new LogoutController();
        return $controller->logout();
    }
}
