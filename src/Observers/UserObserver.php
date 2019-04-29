<?php

namespace ngunyimacharia\openedx\Observers;

use App\User;
use ngunyimacharia\openedx\Models\EdxAuthUser;
use ngunyimacharia\openedx\Models\PasswordReset;

class UserObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        die;
        //Validate user
        if ($user === null) {
            return;
        }
        //Package data to be sent
        $data = [
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->first_name,
            'username' => $user->username,
            'honor_code' => 'true',
            'password' => request()->get(env('PASSWORD_FIELD')),
            'country' => 'KE',
            'terms_of_service' => 'true'
        ];

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'cache-control' => 'no-cache',
            'Referer' => env('MICROSITE_URL') . '/register',
        );

        $client = new \GuzzleHttp\Client();

        try {

            $response = $client->request('POST', env('LMS_REGISTRATION_URL'), [
                'form_params' => $data,
                'headers' => $headers,
            ]);


            return true;
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            //Error, delete user
            $user->delete();

            $responseJson = $e->getResponse();
            $response = json_decode($responseJson->getBody()->getContents(), true);

            //Delete password resets
            PasswordReset::where('email', '=', $user->email)->delete();
            $errors = [];
            foreach ($response as $key => $error) {
                //Return error
                $errors[] = $error;
            }
            echo "CATCH 1";
            return redirect()->back()->withErrors($errors[0]);
        } catch (\Exception $e) {

            //Error, delete user
            $user->delete();
            //Delete password resets
            PasswordReset::where('email', '=', $user->email)->delete();
            echo "CATCH 2";
            echo $e->getMessage();
            return redirect()->back()->withErrors("There was a problem creating your account. Please try again later or report to support.");
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
}
