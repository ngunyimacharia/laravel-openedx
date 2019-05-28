<?php

namespace ngunyimacharia\openedx\Controllers;

use ngunyimacharia\openedx\Models\EdxAuthUser;
use ngunyimacharia\openedx\Models\PasswordReset;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;

class EdxRegisterController extends Controller
{

    public function __construct($user)
    {
        $this->user = $user;
    }
    public function register()
    {
        //Validate user
        if ($this->user->email === null) {
            return;
        }
        //Package data to be sent
        $data = [
            'email' => $this->user->email,
            'name' => $this->user->first_name . ' ' . $this->user->last_name,
            'username' => $this->user->username,
            'honor_code' => 'true',
            'password' => request()->get(env('REGISTER_PASSWORD_FIELD')),
            'country' => 'KE',
            'terms_of_service' => 'true'
        ];

        //Check username
        while (EdxAuthUser::where('username', $this->user->username)->count()) {
            $this->user->username = $this->user->username . random_int(0, 9999);
            $this->user->save();
        }

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'cache-control' => 'no-cache',
            'Referer' => env('LMS_BASE') . '/register',
        );

        $client = new \GuzzleHttp\Client(['verify' => env('VERIFY_SSL', true)]);

        try {

            $response = $client->request('POST', env('LMS_REGISTRATION_URL'), [
                'form_params' => $data,
                'headers' => $headers,
            ]);

            return true;
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            //Delete password resets
            PasswordReset::where('email', '=', $this->user->email)->delete();

            if ($e->getCode() != 500) {

                $response = $e->getResponse();
                $responseBodyAsString = json_decode($response->getBody()->getContents());
                $fields = (array)$responseBodyAsString;
                $returnErrors = [];
                foreach ($fields as $field) {
                    if (!(array)$field) {
                        continue;
                    }
                    foreach ((array)$field as $errors) {
                        if (!(array)$errors) {
                            continue;
                        }
                        foreach ((array)$errors as $error) {
                            $returnErrors[] = $error;
                        }
                    }
                }

                if (count($returnErrors)) {
                    return $returnErrors;
                }
            }
            //Rethrow error
            throw $e;
        }
    }
}
