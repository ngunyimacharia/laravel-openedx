<?php

namespace ngunyimacharia\openedx\Models;

use Illuminate\Database\Eloquent\Model;


class AuthUserprofile extends Model
{

  //set connection for model
  protected $connection = 'edx_mysql';

  //Set table for model
  protected $table = 'auth_userprofile';

  //Disable timestamps
  public $timestamps = false;

  /**
  * @inheritdoc
  */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'meta' => 'Meta',
      'courseware' => 'Courseware',
      'language' => 'Language',
      'location' => 'Location',
      'year_of_birth' => 'Year of Birth',
      'gender' => 'Gender',
      'level_of_education' => 'Level of Education',
      'mailing_address' => 'Mailing Address',
      'city' => 'City',
      'country' => 'Country',
      'goals' => 'Goals',
      'allow_certificate' => 'Allow Certificate',
      'bio' => 'Biography',
      'profile_image_uploaded_at' => 'Profile Image Uploaded At',
      'user_id' => 'User ID'
    ];
  }

  /**
   * @return Model
   */
  public function getUser()
  {
      return $this->hasOne('ngunyimacharia\openedx\EdxAuthUser', 'id','user_id');
  }

}
