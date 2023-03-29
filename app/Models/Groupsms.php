<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupsms extends Model
{
    const TYPE = [
      'single' => 'ارسال تکی',
      'groupsService' => 'گروهی خدمات',
      'groupsPhone' => 'گروهی فهرست شماره ها',
    ];

    protected $guarded = [];

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function phone_list()
    {
        return $this->belongsTo(PhoneList::class);
    }
}
