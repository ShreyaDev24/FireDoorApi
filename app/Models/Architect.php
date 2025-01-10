<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Architect extends Model
{
   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ArcCompanyName','ArcCompanyWebsite','ArcCompanyEmail', 'ArcCompanyPhone','ArcCompanyAddressLine1',
        'ArcCompanyCountry','ArcCompanyState','ArcCompanyCity','ArcCompanyPostalCode','ArcCompanyMoreInfo','UserId'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    //To add user table relationship

    public function users(){
    	return $this->hasMany(User::class,'	ArchitectId');
    }
}