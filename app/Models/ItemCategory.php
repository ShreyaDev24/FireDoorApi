<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'CategoryName','ItemId','CategoryStatus'
    ];

    public function Item(){
        return $this->belongsTo(Item::class,'ItemId','id');
    }
     public function ItemField(){
        return $this->hasMany(ItemField::class,'CategoryId')->orderBy('FieldOrder','asc');;
    }
}
