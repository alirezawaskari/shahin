<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentNotice extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $title = request('title');
        $province = request('province');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $representation = request('representation');
        if (isset($province) && trim($province) != 'all') {
                if($province == 'all_province')
                {
                    $query->whereNull('province_id');
                }
                else
                {
                    $query->where('province_id',$province);
                }
        }

        if (isset($title) && trim($title) != '') {
            $query->where('title', 'LIKE', '%' . $title . '%');
        }

        if (isset($phone) && trim($phone) != '') {
            $query->where('phone', $phone);
        }

        if (isset($representation) && trim($representation) != 'all') {
            $query->where('representation_id', $representation);
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        return $query;
    }

}
