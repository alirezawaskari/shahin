<?php

namespace App\Models;

use App\Helpers\MediaLibraryHelper;
use App\Models\Concerns\AdoptsLegacyMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Promissory extends Model implements AuditableContract, HasMedia
{

    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use InteractsWithMedia;
    use AdoptsLegacyMedia;

    /**
     * Front/back photos of the promissory note. singleFile() => re-upload replaces natively.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('front')->useDisk('public')->singleFile();
        $this->addMediaCollection('back')->useDisk('public')->singleFile();
    }

    /**
     * The photos used to live as File(path, title) rows on files(). Those rows are
     * read-only now: adopted into Spatie on save, never written or deleted.
     */
    protected function legacyFileMap(): array
    {
        return ['front' => 'front', 'back' => 'back'];
    }

    /** Spatie first, else the legacy File row. No such column exists, so this is the reader. */
    public function getFrontImageAttribute(): string
    {
        return MediaLibraryHelper::urlIfFileExists($this, 'front') ?: $this->legacyFileUrl('front');
    }

    public function getBackImageAttribute(): string
    {
        return MediaLibraryHelper::urlIfFileExists($this, 'back') ?: $this->legacyFileUrl('back');
    }

    /**
     * @var string[]
     */
    protected $guarded=['id'];
    /**
     *
     */
    const TITLE = [
        'factor_item_guaranty' => 'ضمانت پرونده',
    ];
    /**
     * statuses of check
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        return [
            '0' => 'رسوب در خزانه',
            '1' => 'عودت داده شده'
        ][$status];
    }
    /**
     * Get all of the promissories 's files.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class, 'historiable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function promissoryable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function applicantable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function exportable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function guarantorable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function paytoable()
    {
        return $this->morphTo();
    }
    /**
     * Get all of the video's assetTransactions.
     */
    public function assetTransactions()
    {
        return $this->morphMany(AssetTransactions::class, 'assetable');
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $exporter=request()->exporter;
        $pay_to=request()->pay_to;
        $start_date=request()->start_date;
        $end_date=request()->end_date;
        $title=request()->title;
        $phone = request('phone');
        $status=request()->status;
        $serial_code=request()->serial_code;

        $exporter_name=request()->exporter_name;
        $exporter_family=request()->exporter_family;
        $exporter_phone=request()->exporter_phone;

        $pay_to_name=request()->pay_to_name;
        $pay_to_family=request()->pay_to_family;
        $pay_to_phone=request()->pay_to_phone;

        $applicant_name=request()->applicant_name;
        $applicant_family=request()->applicant_family;
        $applicant_phone=request()->applicant_phone;
        //check user representation
        if (auth()->user()->admin_representation) {
            $query->where('representation_id', auth()->user()->admin_representation_id);
        }
        //------------------ exporter queries ---------------------
        if (isset($exporter_name) && trim($exporter_name) != '') {
            $query->whereHas('exportable', function ($query)use($exporter_name){
                $query->where('name', 'LIKE', '%' . $exporter_name. '%');
            });
        }

        if (isset($exporter_family) && trim($exporter_family) != '') {
            $query->whereHas('exportable', function ($query)use($exporter_family){
                $query->where('family', 'LIKE', '%' . $exporter_family. '%');
            });
        }

        if (isset($exporter_phone) && trim($exporter_phone) != '') {
            $query->whereHas('exportable', function ($query)use($exporter_phone){
                $query->where('phone', $exporter_phone);
            });
        }
        //----------------pay to queries-----------------
        if (isset($pay_to_name) && trim($pay_to_name) != '') {
            $query->whereHas('paytoable', function ($query)use($pay_to_name){
                $query->where('name', 'LIKE', '%' . $pay_to_name. '%');
            });
        }

        if (isset($pay_to_family) && trim($pay_to_family) != '') {
            $query->whereHas('paytoable', function ($query)use($pay_to_family){
                $query->where('family', 'LIKE', '%' . $pay_to_family. '%');
            });
        }

        if (isset($pay_to_phone) && trim($pay_to_phone) != '') {
            $query->whereHas('paytoable', function ($query)use($pay_to_phone){
                $query->where('phone',$pay_to_phone);
            });
        }
        //---------------- applicant queries-----------------
        if (isset($applicant_name) && trim($applicant_name) != '') {
            $query->whereHas('applicantable', function ($query)use($applicant_name){
                $query->where('name', 'LIKE', '%' . $applicant_name. '%');
            });
        }

        if (isset($applicant_family) && trim($applicant_family) != '') {
            $query->whereHas('applicantable', function ($query)use($applicant_family){
                $query->where('family', 'LIKE', '%' . $applicant_family. '%');
            });
        }

        if (isset($applicant_phone) && trim($applicant_phone) != '') {
            $query->whereHas('applicantable', function ($query)use($applicant_phone){
                $query->where('phone', $applicant_phone );
            });
        }

        if (isset($exporter) && trim($exporter) != '') {
            $query->where('exporter', 'LIKE', '%' . $exporter. '%');
        }
        if (isset($pay_to) && trim($pay_to) != '') {
            $query->where('pay_to', 'LIKE', '%' . $pay_to. '%');
        }
        if (isset($serial_code) && trim($serial_code) != '') {
            $query->where('serial_code', 'LIKE', '%' . $serial_code. '%');
        }

        if (isset($start_date) && trim($start_date) != '' && isset($end_date) && trim($end_date) != '') {
            $query->whereDate('receipt_date', '>=', $start_date)->whereDate('receipt_date', '<=', $end_date);
        }
        if (isset($status) && trim($status) != '' && $status != 'all') {
            $query->whereStatus($status);
        }
        if (isset($title) && trim($title) != '' && $title != 'all') {
            $query->whereTitle($title);
        }
        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('totalFactorItem', function ($query) use ($phone) {
                $query->whereHas('factor',  function ($query) use ($phone){
                    $query->whereHas('user', function ($query) use ($phone) {
                        $query->where('phone', $phone);
                    });
                });
            });
        }
        return $query;
    }
}
