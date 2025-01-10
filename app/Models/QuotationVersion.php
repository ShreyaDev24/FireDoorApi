<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class QuotationVersion extends Model
{
   use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'quotation_versions';
    protected $fillable = [
        'quotation_id','version','status','is_deleted',
    ];

}
