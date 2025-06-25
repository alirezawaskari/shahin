<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayChoose extends Model
{
    use HasFactory;
    protected $table = 'gateways';
    protected $fillable = [
        'name',
        'status'
    ];
    protected $appends = ['status_label'];

    public function getStatusLabelAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }
}
