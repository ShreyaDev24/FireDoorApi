<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LippingSpeciesItems extends Model {
    
    protected $fillable = [];

    public function lipping_species()
    {
        return $this->belongsTo(LippingSpecies::class, 'lipping_species_id', 'id');
    }

    public function selected_lipping_species_items()
    {
        return $this->hasMany(SelectedLippingSpeciesItems::class, 'selected_lipping_species_items_id', 'id');
    
    }
   
}
