<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ArrangeContract extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-file column -> Spatie single-file collection. */
    protected function legacyMediaMap(): array
    {
        return ['file' => 'file'];
    }

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->useDisk('public')->singleFile();
    }

    public function getFileAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'file')
            ?: ($this->getRawOriginal('file') ? asset($this->getRawOriginal('file')) : '');
    }

    /**
     *
     */
    const STATUS = [
        '0' => 'پیش نویس', '1' => 'منتظر تایید مدیر', '2' => 'تایید شده'
    ];

    /**
     * @var string[]
     */
    protected $fillable=['contract_id','file','status','user_id','owner_id','no'];


    /**
     * @param $id
     * @return bool
     */
    public static function ownerArrange($id)
    {
        $arrange_contract = ArrangeContract::where('id',$id)->where('user_id',\Auth::user()->id)->count();
        if($arrange_contract == 1){ return true; }
        return false;
    }

}
