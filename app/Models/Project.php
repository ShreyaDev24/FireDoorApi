<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    
    protected $table = 'project';
    protected $fillable = [
        'ProjectName','UserId','Status'
    ];
  
    
}
