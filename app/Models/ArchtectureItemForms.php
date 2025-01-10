<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ArchtectureItemForms extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'archtecture_item_forms';
    protected $fillable = [
        'UserId','FormName','FileName','Status','FieldValue','Status'
    ];

    public function QuatationFile(){
        return $this->hasMany(QuatationFile::class,'id');
    }
}
