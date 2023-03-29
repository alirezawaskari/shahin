<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function recruitment_notice()
    {
        return $this->belongsTo(RecruitmentNotice::class);
    }

    public function histories()
    {
        return $this->morphMany(History::class, 'historiable');
    }

    public function scopeFilter($query)
    {
        $name = request('name');
        $phone = request('phone');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $job_title = request('job_title');
        $status = request('status');

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

        if (auth()->user()->hasRole('legal')) {
            $query->where('recruitment_notice_id', '25');
        }

        if (auth()->user()->hasRole('superadmin-callcenter')) {
            $query->where('recruitment_notice_id', '27')->orWhere('recruitment_notice_id', '14');
        }

        return $query;
    }

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
