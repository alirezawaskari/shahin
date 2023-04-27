<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryStoreRoom extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['title'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function getCategoryStoreRoom($id, $field)
    {
        if($id){ $cat=CategoryStoreRoom::find($id); if($cat){ return $cat->$field;} }
        return '';
    }

}
