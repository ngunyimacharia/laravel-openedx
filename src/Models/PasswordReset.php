<?php

namespace ngunyimacharia\openedx\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model {
    protected $table = 'password_resets';
    protected $fillable = ['email', 'token'];
      /*
      * The events map for the model
      *
      * @var array
      */
      protected $dispachesEvents = [
        'created'=> PasswordResetCreated::class,
        'updated'=> PasswordResetUpdated::class
      ];
}