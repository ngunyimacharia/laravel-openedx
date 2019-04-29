<?php

namespace ngunyimacharia\openedx\Models;

use Illuminate\Database\Eloquent\Model;


class EdxAuthUser extends Model
{

  //set connection for model
  protected $connection = 'edx_mysql';

  //Set table for model
  protected $table = 'auth_user';

  //Disable timestamps
  public $timestamps = false;

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'password' => 'Password',
      'last_login' => 'Last Login',
      'is_superuser' => 'Is Superuser',
      'username' => 'Username',
      'first_name' => 'First Name',
      'last_name' => 'Last Name',
      'email' => 'Email',
      'is_staff' => 'Is Staff',
      'is_active' => 'Is Active',
      'date_joined' => 'Date Joined',
    ];
  }

  /**
   * @return Model
   */
  public function authRegistration()
  {
    return $this->hasOne('ngunyimacharia\openedx\Models\AuthRegistration', 'user_id', 'id');
  }
  /**
   * @return Model
   */
  public function profile()
  {
    return $this->hasOne('ngunyimacharia\openedx\Models\AuthUserprofile', 'user_id', 'id');
  }

  public function user()
  {
    return $this->hasOne('App\User', 'slug', 'username');
  }

  public function enrollments()
  {
    return $this->hasMany('ngunyimacharia\openedx\Models\StudentCourseEnrollment', 'user_id');
  }
}
