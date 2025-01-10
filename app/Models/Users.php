<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'FirstName','LastName','UserEmail','UserImage', 'UserPhone','UserCompanyPhone','UserCompanyExtension',
        'UserJobtitle','UserMoreInfo','password','UserType','remember_token'
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
    	return $this->hasMany(User::class,'CompanyId');
    }
}
