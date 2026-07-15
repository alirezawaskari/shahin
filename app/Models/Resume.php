<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $casts = [
        'detail' => 'array',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recruitment_notice()
    {
        return $this->belongsTo(RecruitmentNotice::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class, 'historiable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $phone = request('phone');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $job_title = request('job_title');
        $status = request('status');
        $province = request('province');
        $representation = request('representation');

        if (isset($province) && trim($province) != 'all') {
            $query->whereHas('recruitment_notice',function ($query)use($province){
                $query->where('province_id',$province);
            });
        }

        if (isset($representation) && trim($representation) != 'all') {
            $query->where('representation_id',$representation);
        }
        if (isset($name) && trim($name) != '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if (isset($phone) && trim($phone) != '') {
            $query->where('phone', $phone);
        }

        if (isset($job_title) && trim($job_title) != 'all') {
            $query->where('recruitment_notice_id', $job_title);
        }

        if (isset($status) && trim($status) != '' && $status != 'all') {
            $query->whereStatus($status);
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query;
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        switch ($status) {
            case 0:
                return 'جدید';
                break;
            case 1:
                return 'مصاحبه اولیه';
                break;
            case 2:
                return 'تعلیق شده';
                break;
            case 3:
                return 'تایید شده';
                break;
            case 4:
                return 'رد شده';
                break;
            default:
            return 'جدید';
        }
    }
}
