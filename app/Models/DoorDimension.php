<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoorDimension extends Model
{
    
    protected $table = 'door_dimension';
    protected $fillable = [
        'code','UserId','inch_height','inch_width','mm_height','mm_width','fire_rating','door_leaf_finish','cost_price','selling_price','image'
    ];
  
    
}