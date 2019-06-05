<?php

namespace ngunyimacharia\openedx\Controllers;

use App\Http\Controllers\Controller;

use ngunyimacharia\openedx\Models\EdxAuthUser;
use ngunyimacharia\openedx\Models\StudentCourseEnrollment;

use Auth;

class EdxEnrollmentController extends Controller
{

    public static function all()
    {
        $enrollments = StudentCourseEnrollment::all();
        dd($enrollments);
    }

    public static function getCourseProgress($courseId)
    {

        $user = EdxAuthUser::where('email', Auth::user()->email)->firstOrFail();
        $enrollment = StudentCourseEnrollment::where(['user_id' => $user->id, 'course_id' => $courseId])->first();
        if ($enrollment) {
            $enrollment->getGenCert();
            return [
                'status' => $enrollment->status,
                'grade' => (float)$enrollment->grade,
            ];
        } else {
            return ['status' => 'Pending', 'grade' => '0'];
        }
    }
}
