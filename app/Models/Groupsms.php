<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupsms extends Model
{
    /**
     *types of group sms
     */
    const TYPE = [
      'single' => 'ارسال تکی',
      'groupsService' => 'گروهی خدمات',
      'groupsPhone' => 'گروهی فهرست شماره ها',
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phone_list()
    {
        return $this->belongsTo(PhoneList::class);
    }
}
