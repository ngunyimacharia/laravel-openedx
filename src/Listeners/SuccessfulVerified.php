<?php

namespace ngunyimacharia\openedx\Listeners;

use Illuminate\Support\Facades\Auth;
use Toastr;

class SuccessfulVerified
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
        if (Auth::check()) {
            Toastr::success('Account verification successful. Please login to continue');
            Auth::logout();
            return redirect('/');
        }
    }
}
