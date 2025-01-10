<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LippingSpecies extends Model {
    
    protected $fillable = [
        'SpeciesName','MinValue','MaxValues','Status'     
    ];

    public function lipping_species_items()
    {
        return $this->hasMany(LippingSpeciesItems::class, 'lipping_species_id', 'id');
    
    }

   
}
