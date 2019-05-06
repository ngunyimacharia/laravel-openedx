<?php

namespace ngunyimacharia\openedx;
use Carbon\Carbon;

class openedx
{
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
}
