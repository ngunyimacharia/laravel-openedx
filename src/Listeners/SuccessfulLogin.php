<?php

namespace ngunyimacharia\openedx\Listeners;

use Toastr;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Cookie;

class SuccessfulLogin
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
        $webcookies = request()->cookie();
        $email = $event->user->email;
        $password = request()->get(env('LOGIN_PASSWORD_FIELD'));
        //Get CSRF Token
        $client = new \GuzzleHttp\Client(['verify' => env('VERIFY_SSL', true)]);
        try {
            $response = $client->request('GET', env('LMS_LOGIN_URL'));
        } catch (\Exception $e) {
            Toastr::error("There was a problem logging you in. Please try again later or report to support.");
        }
        $csrfToken = null;
        foreach ($response->getHeader('Set-Cookie') as $key => $cookie) {
            if (strpos($cookie, 'csrftoken') === FALSE) {
                continue;
            }
            $csrfToken = explode('=', explode(';', $cookie)[0])[1];
            break;
        }
        if (!$csrfToken) {
            //Error, reactivate reset
            Toastr::error("There was a problem logging you in. Please try again later or report to support.");
        }
        $data = [
            'email' => $email,
            'password' => $password,
        ];
        $headers = [
            'Content-Type' => ' application/x-www-form-urlencoded ; charset=UTF-8',
            'Accept' => ' text/html,application/json',
            'X-CSRFToken' => $csrfToken,
            'Cookie' => ' csrftoken=' . $csrfToken,
            'Origin' => env('LMS_BASE'),
            'Referer' => env('LMS_BASE') . '/login',
            'X-Requested-With' => ' XMLHttpRequest',
        ];
        $client = new \GuzzleHttp\Client(['verify' => env('VERIFY_SSL', true)]);
        $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray([
            'csrftoken' => $csrfToken
        ], env('LMS_DOMAIN'));

        try {
            $response = $client->request('POST', env('LMS_LOGIN_URL'), [
                'form_params' => $data,
                'headers' => $headers,
                'cookies' => $cookieJar
            ]);
            //set cookies
            if (!$response->hasHeader('Set-Cookie')) {
                Toastr::error('Error getting Course Authentication');
            }
            $loggedInCookies = $response->getHeader('Set-Cookie');
            $ourCookies = [];
            foreach ($loggedInCookies as $userCookie) {
                //format cookies
                $cookieDetails = (explode(';', $userCookie));
                $ourCookie = [];
                foreach ($cookieDetails as $cookieDetail) {
                    $key = strtolower(trim(explode('=', $cookieDetail)[0]));
                    $value = isset(explode('=', $cookieDetail)[1]) ? trim(explode('=', $cookieDetail)[1]) : 1;
                    if (
                        in_array(
                            strtolower($key),
                            ['__cfduid', 'csrftoken', 'edxloggedin', 'sessionid', 'openedx-language-preference', 'edx-user-info']
                        )
                    ) {
                        $ourCookie['name'] = $key;
                        $ourCookie['value'] = $value;
                    } else {
                        $ourCookie[$key] = $value;
                    }
                }
                if ($ourCookie['name'] != 'edx-user-info' && !isset($ourCookie['domain'])) {
                    if ($ourCookie['name'] == 'csrftoken') {
                        $ourCookie['domain'] = '';
                    } else {
                        $ourCookie['domain'] = env('APP_DOMAIN');
                    }
                    //Set the cookie
                    setrawcookie($ourCookie['name'], $ourCookie['value'], 0, '/', $ourCookie['domain']);
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Toastr::error($responseBodyAsString);
        }
        $data = [
            'grant_type' => 'password',
            'client_id' => env('EDX_KEY'),
            'client_secret' => env('EDX_SECRET'),
            'username' => auth()->user()->username,
            'password' => $password,
            //'token_type'=>'jwt',
        ];
        $tokenUrl = env('LMS_BASE') . '/oauth2/access_token/';
        //Get authorization token
        $accessResponse = Curl::to($tokenUrl)
            ->withData($data)
            ->withResponseHeaders()
            ->returnResponseObject()
            ->post();
        dd($accessResponse);
        if ($accessResponse->status !== 200) {
            Toastr::error('Authentication Error: Username does not exist or invalid credentials given');
        }
        //Set access token
        $accessToken = json_decode($accessResponse->content, true);
        Cookie::queue(Cookie::make('edinstancexid', $accessToken['access_token'], $accessToken['expires_in']));
        return true;
    }
}