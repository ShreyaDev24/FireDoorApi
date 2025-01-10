<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemField extends Model
{
   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'FieldName','CategoryId','FieldType','FieldOrder','FieldStatus','DefaultValueNumber','DefaultValueText',
        'Instruction','Price','MinValue','MaxValue','FiledValidation','Required','ReadOnly',
        'HideField','Minheight','Maxheight','Minwidth','Maxwidth','FontSize','Heading','Options',
    ];

    public function ItemCategory(){
        return $this->belongsTo(ItemCategory::class,'CategoryId','id');
    }
}
