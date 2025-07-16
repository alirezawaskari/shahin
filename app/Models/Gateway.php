<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $table = 'gateways';
    protected $fillable = [
        'name',
        'fa_name',
        'logo',
        'status'
    ];
    protected $appends = ['status_label'];

    public function getStatusLabelAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }
}
