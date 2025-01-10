<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class QuotationVersionItems extends Model
{
   use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'quotation_version_items';
    protected $fillable = [
        'QuotationId','itemID','Version','Status','IsDeleted',
    ];
      /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    public function ItemCategory(){
        return $this->hasMany(ItemCategory::class,'ItemId');
    }
}
