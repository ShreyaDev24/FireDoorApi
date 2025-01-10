<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamBoard extends Model
{
    protected  $table = 'team_boards';
    protected  $fillable = ['ProjectId','UserId','Message'];
    protected $time;
    protected $user;
}
