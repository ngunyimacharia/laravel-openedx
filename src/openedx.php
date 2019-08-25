<?php

namespace ngunyimacharia\openedx;

use Carbon\Carbon;
use Cookie;
use Auth;
use Toastr;

use ngunyimacharia\openedx\Controllers\EdxRegisterController as RegisterController;
use ngunyimacharia\openedx\Controllers\EdxLoginController as LoginController;
use ngunyimacharia\openedx\Controllers\EdxLogoutController as LogoutController;
use ngunyimacharia\openedx\Controllers\EdxEnrollmentController as EnrollmentController;

class openedx
{

    public function register($user)
    {
        $controller = new RegisterController($user);
        return $controller->register();
    }

    public function login($credentials)
    {
        $controller = new LoginController($credentials);
        return $controller->login();
    }

    public function logout()
    {
        $controller = new LogoutController();
        return $controller->logout();
    }

    public function getCourses()
    {

        $client = new \GuzzleHttp\Client();
        try {

            $response = $client->request('GET', env('LMS_BASE') . '/api/courses/v1/courses/?page_size=1000');
            $courses =  json_decode($response->getBody()->getContents())->results;
            foreach ($courses as $key => $value) {
                $course = (array)$value;
                $courses[$key] = (array)$courses[$key];
                $course['overview'] = $this->getOverview($course);

                //Remove unwanted fields
                $course['course_video_uri'] = $course['media']->course_video->uri;
                $course['course_image_uri'] = env('LMS_BASE') . $course['media']->course_image->uri;
                unset($course['media']);
                unset($course['course_id']);
                //Format datetime
                $course['start'] = date('Y-m-d H:m:i', strtotime($course['start']));
                $course['end'] = date('Y-m-d H:m:i', strtotime($course['end']));
                $course['enrollment_start'] = date('Y-m-d H:m:i', strtotime($course['enrollment_start']));
                $course['enrollment_end'] = date('Y-m-d H:m:i', strtotime($course['enrollment_end']));
                //Format time
                $exploded_effort = explode(":", $course['effort']);
                switch (count($exploded_effort)) {
                    case '3':
                        $course['effort'] = Carbon::createFromTime($exploded_effort[0], $exploded_effort[1], $exploded_effort[2])->toTimeString();
                        break;
                    case '2':
                        $course['effort'] = Carbon::createFromTime(0, $exploded_effort[0], $exploded_effort[1])->toTimeString();
                        break;
                }
                $courses[$key] = $course;
            }
            return $courses;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseJson = $e->getResponse();
            $response = $responseJson->getBody()->getContents();
            dd($response);
        }
    }

    private function getOverview($course)
    {

        $client = new \GuzzleHttp\Client();
        try {

            //Get course description
            $request = $client->request('GET', env('LMS_BASE') . '/api/courses/v1/courses/' . $course['id']);
            $response = json_decode($request->getBody()->getContents());
            return $response->overview;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseJson = $e->getResponse();
            $response = $responseJson->getBody()->getContents();
            dd($response);
        }
    }

    /*
  * Function to get enrollment status
  */
    public function checkEnrollmentStatus($course_id)
    {
        if (!isset($_COOKIE['edinstancexid'])) {
            Auth::logout();
            return false;
        }
        $client = new \GuzzleHttp\Client(
            [
                'verify' => env('VERIFY_SSL', true),
                'headers' => [
                    'Authorization' => 'Bearer ' . $_COOKIE['edinstancexid']
                ]
            ]
        );

        $request = $client->request('GET', env('LMS_BASE') . '/api/enrollment/v1/enrollment/' . Auth::user()->username . ',' . $course_id);
        $response = json_decode($request->getBody()->getContents());

        if ($response && $response->is_active == true) {
            $enrollmentStatus = true;
        } else {
            $enrollmentStatus = false;
        }
        return $enrollmentStatus;
    }

    /** 
     *Function to enroll a user
     */
    public function enroll($course_id)
    {


        if ($this->checkEnrollmentStatus($course_id)) {
            return Toastr::error("You're already enrolled to this course");
        }

        $courseInfoObject = new \stdClass();
        $courseInfoObject->course_id = $course_id;

        $enollAttributesObject = new \stdClass();
        $enollAttributesObject->namespace = 'honor';
        $enollAttributesObject->name = env('APP_NAME');
        $enollAttributesObject->value = env('APP_NAME');

        $enrollmentInfoObject = new \stdClass();
        $enrollmentInfoObject->user = Auth::user()->slug;
        $enrollmentInfoObject->mode = 'honor';
        $enrollmentInfoObject->is_active = true;
        $enrollmentInfoObject->course_details = $courseInfoObject;
        $enrollmentInfoObject->enrollment_attributes = [$enollAttributesObject];


        $enrollClient = new \GuzzleHttp\Client(
            [
                'verify' => env('VERIFY_SSL', true),
                'headers' => [
                    'Authorization' => 'Bearer ' . $_COOKIE['edinstancexid']
                ]
            ]
        );

        try {
            $response = $enrollClient->request('POST', env('LMS_BASE') . '/api/enrollment/v1/enrollment', [
                \GuzzleHttp\RequestOptions::JSON => $enrollmentInfoObject
            ]);

            return true; //Toastr::success("You have successfully enrolled into this course");

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            // dd($responseBodyAsString);

            return Toastr::error("Error enrolling into course");

            return false;
        }
    }


    /**
     *Get all enrollments
     */

    public function enrollments()
    {
        EnrollmentController::all();
    }

    /**
     * Get user course progress
     */
    public function getCourseProgress($courseId)
    {
        return EnrollmentController::getCourseProgress($courseId);
    }
}
