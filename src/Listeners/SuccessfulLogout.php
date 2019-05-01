<?php

namespace ngunyimacharia\openedx\Listeners;

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
        //logout
        $cookies = ['csrftoken', 'edxloggedin', 'edx-user-info', 'openedx-language-preference', 'edinstancexid', 'sessionid'];
        foreach ($_COOKIE as $name => $value) {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
                setcookie($name, '', time() - 1000, '/');
            }
        }
        return true;
    }
}
