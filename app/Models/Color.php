<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Validator;

class Color extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'color';
    protected $fillable = [
        'ColorName','RGB','Hex','EnglishName','status','UserId'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    
}
