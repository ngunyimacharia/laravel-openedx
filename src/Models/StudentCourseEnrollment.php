<?php

namespace ngunyimacharia\openedx\Models;

use ngunyimacharia\openedx\Models\GeneratedCertificate;
use Illuminate\Database\Eloquent\Model;
use DB;

class StudentCourseEnrollment extends Model
{
  //set connection for model
  protected $connection = 'edx_mysql';

  //Set table for model
  protected $table = 'student_courseenrollment';

  public function edx_user(){
    return $this->belongsTo('App\Edx\EdxAuthUser','user_id');
  }

  public function course(){
    return $this->belongsTo('App\Courses');
  }

  public function getGenCert(){
       $genCert = GeneratedCertificate::where('course_id',$this->course_id)->where('user_id',$this->user_id)->first();
       if($genCert){
          $this->g = $genCert->grade;
          $this->grade = ($genCert->grade * 100).'%';
         switch ($genCert->status) {
           case 'downloadable':
              $this->status = "Completed";
             break;
           case 'notpassing':
           default:
             $this->status = "Completed but failed";
             break;
         }

       }else{
         $this->status = 'In progress';
         $this->grade = (0).'%';
       }

    return $genCert;
  }


  public function getGenRept(){
    $genRept = GeneratedCertificate::where('user_id',$this->user_id)->where('status','downloadable')->first();
    return $genRept;
  }


}
