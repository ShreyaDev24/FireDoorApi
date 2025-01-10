<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonConfigurableItems extends Model
{
    protected $table = 'non_configurable_items';

   protected $fillable = [
       "name",
       "image",
       "description",
       "price",
       "created_at",
       "updated_at"
   ];
}