<?php

namespace ngunyimacharia\openedx\Controllers;

use App\Http\Controllers\Controller;

class EdxLogoutController extends Controller
{
    public function __construct()
    {
        
    }

    public function logout()
    {

        // unset cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }

        //Delete other cookies
        $cookies = ['_cfduid', 'edinstancexid', 'edxloggedin', 'openedx-language-preference', 'sessionid'];
        foreach ($cookies as $cookie) {
            setrawcookie(
                $cookie,
                '',
                time() - 1000,
                '/',
                '.' . env('MICROSITE_BASE'),
                FALSE,
                FALSE
            );
        }
        return true;
    }
}
