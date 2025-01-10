<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'CompanyName','CompanyWebsite','CompanyPhoto','CompanyEmail', 'CompanyPhone','CompanyVatNumber','CompanyAddressLine1','UserId',
        'CompanyAddressLine2','CompanyAddressLine3','CompanyCountry','CompanyState','CompanyCity','CompanyPostalCode','CompanyMoreInfo'
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


}
