<?php

namespace ngunyimacharia\openedx\Observers;

use App\User;
use ngunyimacharia\openedx\Models\EdxAuthUser;
use ngunyimacharia\openedx\Models\PasswordReset;
use Ixudra\Curl\Facades\Curl;
use Toastr;

class UserObserver
{
    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updating(User $user)
    {
        $password = request()->get('password');
        if ($password) {
            return $this->resetEdxPassword($user, $password);
        }
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        /*
        *Update the following details of edx user: first_name, last_name, email, active
        */

        //get edxuser
        $edxuser = EdxAuthUser::where('username', $user->username)->first();
        if ($edxuser === null || $edxuser->authRegistration->activation_key === null) {
            return redirect()->back()->withErrors("There was a problem updating your account. Please try again later or report to support.");
        }
        //Update edxuser names and active
        $edxuser->first_name = $user->first_name;
        $edxuser->last_name = $user->last_name;
        $edxuser->email = $user->email;
        $edxuser->is_active = $user->email_verified_at ? 1 : 0;
        $edxuser->save();
        //Update user profile name
        $profile = $edxuser->profile;
        $profile->name = $user->first_name . ' ' . $user->last_name;
        return $profile->save();
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }


    private function resetEdxPassword($user, $password)
    {
        //if password reset, perform edx password resets
        $email = $user->email;

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', env('LMS_RESET_PASSWORD_PAGE'));
        } catch (\Exception $e) {
            return Toastr::error("There was a problem resetting your password. Please try again later or report to support.");
        }
        if ($response->hasHeader('Set-Cookie')) {
            $csrfToken = explode('=', explode(';', $response->getHeader('Set-Cookie')[0])[0])[1];
        } else {
            //Error, reactivate reset
            return Toastr::error("There was a problem resetting your password. Please try again later or report to support.");
        }


        $data = [
            'email' => $email,
            'password' => $password,
            'csrfmiddlewaretoken' => $csrfToken
        ];

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'cache-control' => 'no-cache',
            'Referer' => env('APP_URL') . '/register',
        );

        $client = new \GuzzleHttp\Client();
        try {

            $response = $client->request('POST', env('LMS_RESET_PASSWORD_API_URL'), [
                'form_params' => $data,
                'headers' => $headers,
            ]);
            return true;
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseJson = $e->getResponse();
            dd($e->getResponse());
            $response = $responseJson->getBody()->getContents();
            //Error, reactivate reset
            return Toastr::error("There was a problem resetting your password. Please try again later or report to support.");
        } catch (\Exception $e) {
            //Error, reactivate reset
            return Toastr::error("There was a problem resetting your password. Please try again later or report to support.");
        }
    }
}
