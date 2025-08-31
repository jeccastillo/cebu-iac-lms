<?php

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionCampaignContactResponse extends Model
{
    use HasFactory;

    public function campaign()
    {
        return $this->belongsTo(AdmissionCampaign::class, 'campaign_id', 'id');
    }
}
