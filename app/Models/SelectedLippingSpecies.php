<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectedLippingSpecies extends Model {
    
    protected $fillable = ['SelectedSpeciesName','SelectedMinValue','SelectedMaxValues','SelectedStatus'];
       
}
