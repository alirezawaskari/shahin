<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Session extends Model implements AuditableContract
{
    use SoftDeletes;
    use Auditable;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

        /**
     * @param FactorItem $factor_item
     * @return void
     */
    public static function checkAndStartEntryVisitSession(FactorItem $factor_item)
    {
        $session=Session::whereIn('status',[3,4,5,6])->where('factor_item_id',$factor_item->id)->first();
        if (is_null($session) ){
          $session=  Session::create([
                'status' => 3,
                'user_id' => auth()->user()->id,
                'factor_item_id' => $factor_item->id,
                'date' => Carbon::now(),
                'description_before' => 'ویزیت اصلی / مراجعه حضوری کاربر .',
            ]);
        }
        if (!is_null($session)){
            self::userSessionStartEntry($session);
        }
    }

    /**
     * @param FactorItem $factor_item
     * @param int $int
     * @return void
     */
    public static function changeVisitStatusByFactorItem(FactorItem $factor_item, int $int)
    {
      $session= Session::whereIn('status',[3,5])->where('factor_item_id',$factor_item->id);
           if (!is_null($session)){
               $session->update(
                   [
                       'status'=>$int
                   ]
               );
           }

    }

    /**
     * @param $factor_item
     * @return void
     */
    public static function checkAndEndEntrySession($factor_item)
    {
        $session=Session::whereIn('status',[3,4,5])->where('factor_item_id',$factor_item->id)->latest()->first();
        if (!is_null($session)){
            self::userSessionEndEntry($session);
        }
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factorItemServiceTariff()
    {
        return $this->belongsTo(FactorItemServiceTariff::class, 'factor_item_service_tariff_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factorItem()
    {
        return $this->belongsTo(FactorItem::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function userPerformances()
    {
        return $this->belongsToMany(UserPerformance::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function confirmer()
    {
        return $this->belongsTo(User::class , 'confirmer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    /**
     * Get the entries
     */
    public function entries()
    {
        return $this->morphMany(Entry::class, 'entryable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $family = request('family');
        $phone = request('phone');
        $startDate = request('start_date');
        $endDate = request('end_date');
        $user = request('user');
        $status = request('status');
        $center = request('center');
        $employee = request('employee');

        if (auth()->user()->admin_representation) {
            $representation = auth()->user()->admin_representation->id;
            $query->whereHas('factorItem', function ($query) use ($representation) {
                $query->whereHas('factor', function ($query) use ($representation) {
                    $query->where('representation_id', $representation);
                });
            });
        }

        if (auth()->user()->center_id) {
            $center = auth()->user()->center_id;
            $query->whereHas('factorItem', function ($query) use ($center) {
                $query->where('center_id', $center);
            });
        }

        if (auth()->user()->admin_organization) {
            $organization = auth()->user()->admin_organization;
            $query->whereHas('factorItem', function ($query) use ($organization) {
                $query->whereHas('factor', function ($query) use ($organization) {
                    $query->whereIn('organization_id', auth()->user()->admin_organization->organ_code);
                });
            });
        }

        if (auth()->user()->hasPermission('session')) {
            $query->where('agent_id', auth()->user()->id);
        }

        if (isset($name) && trim($name) != '') {
            $query->whereHas('factorItem', function ($query) use ($name) {
                $query->whereHas('factor', function ($query) use ($name) {
                    $query->whereHas('user', function ($query) use ($name) {
                        $query->where('name', 'LIKE', '%' . $name . '%');
                    });
                });
            });
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('factorItem', function ($query) use ($family) {
                $query->whereHas('factor', function ($query) use ($family) {
                    $query->whereHas('user', function ($query) use ($family) {
                        $query->where('family', 'LIKE', '%' . $family . '%');
                    });
                });
            });
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('factorItem', function ($query) use ($phone) {
                $query->whereHas('factor', function ($query) use ($phone) {
                    $query->whereHas('user', function ($query) use ($phone) {
                        $query->where('phone', $phone);
                    });
                });
            });
        }

        if (isset($user) && trim($user) != '') {
            $query->where('user_id', $user);
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereDate('date', '>=' ,$startDate)->whereDate('date', '<=' ,$endDate);
        }

        if (isset($status) && trim($status) != '' && $status != 'all') {
            $query->whereStatus($status);
        }
        if (isset($employee) && $employee != 'all') {
            $query->where('agent_id',$employee);
        }

        if (isset($center) && $center != 'all') {
            $query->whereHas('factorItem', function ($query) use ($center) {
                $query->where('center_id', $center);
            });
        }
        return $query;
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        return [
            '0' => 'پیش جلسه',
            '1' => 'انجام شده',
            '2' => 'منتظر تایید',
            '3' => 'در انتظار مشاوره اولیه',
            '4' => 'ویزیت تایید شده',//Currently not developed
            '5' => 'در انتظار مشاوره اصلی',
            '6' => 'مشاوره شد',
            '7' => 'ویزیت لغو شد',
        ][$status];
    }

    /**
     * @param Session $session
     * @return bool
     */
    public static function userSessionStartEntry(Session $session)
    {
        $output=false;
        if ($session->entries()->count()==0){
            $session->entries()->create([
                'user_id'=>$session->factorItem->factor->user->id,
                'start'=>Carbon::now(),
                'created_by'=>auth()->user()->id
            ]);
            $output=true;
        }
        return $output;
    }


    /**
     * @param Session $session
     * @return bool
     */
    public static function userSessionEndEntry(Session $session)
    {
        $output=false;
        $entry=$session->entries()->latest()->whereNotNull('start')->where('end',null)->first();
        if (!is_null($entry)){
            $entry->update([
                'end'=>Carbon::now(),
                'created_by'=>auth()->user()->id
            ]);
            $output=true;
        }
        return $output;
    }
}
