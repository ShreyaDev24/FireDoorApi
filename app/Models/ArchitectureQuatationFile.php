<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ArchitectureQuatationFile extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'architecture_quatation_file';
    protected $fillable = [
        'filename','generated_id','status'
    ];

    public function QuatationFile(){
        return $this->hasMany(QuatationFile::class,'id');
    }
}
