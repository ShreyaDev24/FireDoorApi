<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'UserId','CstCompanyName','CstCompanyLogo','CstCompanyPhoto','CstCompanyEmail', 'CstCompanyPhone','CstCompanyVatNumber',
        'CstCompanyAddressLine1','CstCompanyAddressLine2','CstCompanyAddressLine3','CstCompanyCountry','CstCompanyState','CstCompanyCity','CstCompanyPostalCode',
        'CstSiteAddressLine1','CstSiteAddressLine2','CstSiteAddressLine3','CstSiteCountry','CstSiteState','CstSiteCity','CstSitePostalCode',
        'CstSiteAvailability','CstDeliveryDay','CstDeliveryFromTime','CstDeliveryToTime','CstDeliveryDeliveryType','CstDeliverySupplyType',
        'CstDeliveryPaymentType','CstCertification','CstMoreInfo'
    ];


    public function CustomerContact(){
    	return $this->hasMany(CustomerContact::class,'CustomerId');
    }
    
}
