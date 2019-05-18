<?php

namespace ngunyimacharia\openedx\Listeners;

use Toastr;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;

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
        $email = request()->get(env('LOGIN_EMAIL_FIELD')) ?: request()->get(env('REGISTER_EMAIL_FIELD'));
        $password = request()->get(env('LOGIN_PASSWORD_FIELD')) ?: request()->get(env('REGISTER_PASSWORD_FIELD'));
        //Get CSRF Token
        $client = new \GuzzleHttp\Client(['verify' => env('VERIFY_SSL', true)]);
        $response = $client->request('GET', env('LMS_LOGIN_URL'));


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
            return;
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


        $response = $client->request('POST', env('LMS_LOGIN_URL'), [
            'form_params' => $data,
            'headers' => $headers,
            'cookies' => $cookieJar
        ]);
        //set cookies
        if (!$response->hasHeader('Set-Cookie')) {
            Toastr::error('Error getting Course Authentication');
            return;
        }
        $loggedInCookies = $response->getHeader('Set-Cookie');
        $setCookies = [];
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
                    $ourCookie['domain'] = '.' . env('MICROSITE_BASE');
                }
                //Set the cookie
                $setCookies[] = ['name' => $ourCookie['name'], 'value' => $ourCookie['value'], 'domain' => $ourCookie['domain']];
            }
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
        if ($accessResponse->status !== 200) {
            Toastr::error('Authentication Error: Username does not exist or invalid credentials given');
            return;
        }
        //Set access token
        $accessToken = json_decode($accessResponse->content, true);
        $setCookies[] = ['name' => 'edinstancexid', 'value' => $accessToken['access_token'], 'expiry' => $accessToken['expires_in']];
        foreach ($setCookies as $cookie) {
            $cookie['expiry'] = 0;
            // if (!isset($cookie['expiry'])) {
            // }
            if (!isset($cookie['domain'])) {
                $cookie['domain'] = ".sustainabilitytraining.tk";
            }
            setrawcookie($cookie['name'], $cookie['value'], $cookie['expiry'], '/', $cookie['domain']);
        }
        return true;
    }
}
