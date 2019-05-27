<?php

namespace ngunyimacharia\openedx\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Toastr;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use function GuzzleHttp\json_decode;

class EdxLoginController extends Controller
{

    public function __construct($credentials)
    {
        //Fetch credentials
        $this->email = $credentials['email'];
        $this->password = $credentials['password'];
        $this->username = DB::table('users')->where('email', $this->email)->first()->username;

        //Fetch login url
        $this->lmsBase = env('LMS_BASE');
        $this->loginUrl =  env('LMS_LOGIN_URL');
        $this->lmsDomain = env('LMS_DOMAIN');
        $this->tokenUrl = $this->lmsBase . '/oauth2/access_token/';

        //Fetch key and secret
        $this->edxKey = env('EDX_KEY');
        $this->edxSecret = env('EDX_SECRET');

        //Get CSRF Token
        $this->getCsrfToken();

        //Get cookieJar
        $this->cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray([
            'csrftoken' => $this->csrfToken
        ], $this->lmsDomain);

        //Store for cookies
        $this->cookies = [];
    }

    public function login()
    {
        //Use credentials to login and get loginCookies
        $this->credLogin();

        //Get access token
        $this->getAccessToken();

        //Set cookies
        return $this->commitCookies();
    }

    private function getCsrfToken()
    {

        //Get CSRF Token
        $client = new \GuzzleHttp\Client(['verify' => env('VERIFY_SSL', true)]);
        $response = $client->request('GET', $this->loginUrl);

        foreach ($response->getHeader('Set-Cookie') as $key => $cookie) {
            if (strpos($cookie, 'csrftoken') === FALSE) {
                continue;
            }
            $this->csrfToken = explode('=', explode(';', $cookie)[0])[1];
            return $this->csrfToken;
        }
        throw new \Exception('Unable to get CSRF Token from LMS');
    }


    private function credLogin()
    {
        $data = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        $headers = [
            'Content-Type' => ' application/x-www-form-urlencoded ; charset=UTF-8',
            'Accept' => ' text/html,application/json',
            'X-CSRFToken' => $this->csrfToken,
            'Cookie' => ' csrftoken=' . $this->csrfToken,
            'Origin' => $this->lmsBase,
            'Referer' => $this->lmsBase . '/login',
            'X-Requested-With' => ' XMLHttpRequest',
        ];

        $client = new \GuzzleHttp\Client(['verify' => env('VERIFY_SSL', true)]);

        $response = $client->request('POST', env('LMS_LOGIN_URL'), [
            'form_params' => $data,
            'headers' => $headers,
            'cookies' => $this->cookieJar
        ]);

        //set cookies
        if (!$response->hasHeader('Set-Cookie')) {
            throw new \Exception('Login cookies not send from LMS.');
        }

        return $this->setCookies($response->getHeader('Set-Cookie'));
    }


    private function setCookies($loggedInCookies)
    {
        $setCookies = [];
        foreach ($loggedInCookies as $userCookie) {
            //format cookies
            $cookieDetails = (explode(';', $userCookie));
            $ourCookie = [];
            foreach ($cookieDetails as $detail) {

                $key = strtolower(trim(explode('=', $detail)[0]));
                $value = isset(explode('=', $detail)[1]) ? trim(explode('=', $detail)[1]) : 1;

                switch (strtolower($key)) {
                    case 'max-age':
                        break;
                    case 'expires':
                        $ourCookie[$key] = Carbon::parse($value)->timestamp;
                        break;
                    case 'path':
                    case 'domain':
                        $ourCookie[$key] = $value;
                        break;
                    case 'secure':
                        $ourCookie['secure'] = TRUE;
                        break;
                    case 'httponly':
                        $ourCookie['httponly'] = TRUE;
                        break;
                    default:
                        $ourCookie['name'] = $key;
                        $ourCookie['value'] = $value;
                        break;
                }
            }
            //Set defaults
            $ourCookie['domain'] = $ourCookie['name'] == 'csrftoken' || $ourCookie['name'] == 'edx-user-info' ? '' : '.' . env('MICROSITE_BASE');
            $ourCookie['secure'] = isset($ourCookie['secure']) ? $ourCookie['secure'] : FALSE;
            $ourCookie['httponly'] = isset($ourCookie['httponly']) ? $ourCookie['httponly'] : FALSE;
            $this->cookies[$ourCookie['name']] = $ourCookie;
        }
        return $this->cookies;
    }

    private function getAccessToken()
    {

        $data = [
            'grant_type' => 'password',
            'client_id' => $this->edxKey,
            'client_secret' => $this->edxSecret,
            'username' => $this->username,
            'password' => $this->password,
        ];
        //Get authorization token
        $accessResponse = Curl::to($this->tokenUrl)
            ->withData($data)
            ->withResponseHeaders()
            ->returnResponseObject()
            ->post();
        if ($accessResponse->status !== 200) {
            throw new \Exception('Authentication Error: Username does not exist or invalid credentials given.');
        }
        //Set access token
        $accessToken = json_decode($accessResponse->content, true);
        $this->cookies['edinstancexid'] = [
            'name' => 'edinstancexid',
            'value' => $accessToken['access_token'],
            'expires' => Carbon::now()->addSeconds($accessToken['expires_in'])->timestamp,
            'domain' => '.' . env('MICROSITE_BASE'),
            'path' => '/',
            'secure' => FALSE,
            'httponly' => FALSE,
        ];

        return $this->cookies;
    }

    private function commitCookies()
    {
        //Remove edx user info
        unset($this->cookies['edx-user-info']);

        foreach ($this->cookies as $cookie) {
            setrawcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['expires'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }
        
        return $this->cookies;
    }
}
