<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectedLippingSpeciesItems extends Model {
    
    protected $fillable = [];

    public function lipping_species_items()
    {
        return $this->belongsTo(LippingSpeciesItems::class, 'selected_lipping_species_items_id', 'id');
    }
   
}
