<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignCollaboration extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'logo',
        'url',
    ];
}
