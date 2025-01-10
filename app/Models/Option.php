<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model {
    
    protected $fillable = [
        'OptionName','OptionSlug','OptionKey','OptionValue' ,'OptionStatus'
    ];

   
}
