<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['title','title_en','description','type','logo','link','registration_form','request_by','status'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public static function getBrand($id,$field)
    {
        if($id != null){$brand=Brand::find($id);if($brand) return $brand->$field;}
        return '';
    }

    /**
     * change status of brand by current status
     * @return void
     */
    public function changeStatus()
    {
        if ($this->status==1){
            $this->update([
                'status'=>0
            ]);
        }else{
            $this->update([
                'status'=>1
            ]);
        }
    }

}
