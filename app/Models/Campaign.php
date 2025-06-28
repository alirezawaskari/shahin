<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'keyword',
        'capacity',
        'content',
        'image',
        'image_header',
        'start_date',
        'end_date',
    ];

    public function campaign_collaboration() : HasMany
    {
        return $this->hasMany(CampaignCollaboration::class,'campaign_id','id',);
    }
}
