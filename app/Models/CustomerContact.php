<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'FirstName','LastName','ContactEmail','CustomerId' ,'ContactType','ContactPhone','ContactJobtitle',
    ];

    public function Customer(){
        return $this->belongsTo(Customer::class,'CustomerId','id');
    }
}
