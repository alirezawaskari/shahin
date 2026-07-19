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
        'usage',
        'content',
        'image',
        'image_header',
        'slug',
        'form',
        'start_date',
        'end_date',
    ];

    public function campaign_collaboration() : HasMany
    {
        return $this->hasMany(CampaignCollaboration::class,'campaign_id','id',);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('usage', '>', 0);
        });
    }
}
