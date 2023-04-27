<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Context extends Model
{
    use SoftDeletes;

    /**
     *key of rules body
     */
    const SITE_RULES_KEYS = [
        'site_rules_body'
    ];

    /**
     * @var string[]
     */
    protected $guarded=['id'];

    /**
     * update context data
     * input Example :
     * array(['key'=>'key_sample','value'=>'value_sample','description'=>'description_sample'],['key'=>'key_sample','value'=>'value_sample','description'=>'description_sample'],...)
     * @param array $input
     * @return void
     * @throws \Exception
     */
    public static function updateContext(array $input)
    {
        foreach ($input as $item){
            if (!isset($item['key']) || is_null($item['key']) || !isset($item['value'])){
                throw new \Exception('the array in defective');
            }
            if (is_null(self::where('key',$item['key'])->first())){
                throw new \Exception("the key is not valid || defective key : {$item['key']}");
            }
        }
        foreach ($input as $item){
           $result_array= array_intersect_key($item,['key'=>null,'value'=>null,'description'=>null]);
           self::where('key' , $item['key'])->update(
               $result_array
           );
        }
   }

    /**
     * @param array $keys
     * @return mixed
     */
    public static function fetchByKeys(array $keys)
    {
        return Context::whereIn('key',$keys)->get();
    }

    /**
     * @param Collection $contexts
     * @return array
     */
    public static function convertFetchedContextToKeyValue(Collection $contexts):array
    {
       $data=array();
        foreach ($contexts as $context) {
            $data[$context->key]=$context->value;
       }
        return $data;
    }

}
