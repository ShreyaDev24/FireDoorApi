<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IronmongeryInfoModel extends Model
{
    
    protected $table = 'ironmongery_info';
    protected $fillable = [
        'Name','Code','Status','UserId'
    ];
  
    
}
