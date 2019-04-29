<?php

namespace ngunyimacharia\openedx\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedCertificate extends Model
{
  //set connection for model
  protected $connection = 'edx_mysql';

  //Set table for model
  protected $table = 'certificates_generatedcertificate';

  //Disable timestamps
  public $timestamps = false;

}
