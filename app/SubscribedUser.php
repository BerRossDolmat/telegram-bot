<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscribedUser extends Model
{
    use SoftDeletes;

    protected $fillable = ['telegram_id', 'first_name', 'last_name', 'username'];

    protected $table = 'subscribed_users';

    protected $dates = ['deleted_at'];
    //
}
