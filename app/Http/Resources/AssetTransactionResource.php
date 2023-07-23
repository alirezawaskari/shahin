<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $receiver =  $this->fetchReceiver($this);
        return array(
                  'sort_by' => 'created_at',
                   'id' => $this->id,
                    'asset' =>collect( explode("\\",$this->assetable_type))->last(),
                   'receiver_id'=>$receiver['id'],
                    'receiver_type'=>$receiver['type'],
                    'receiver_name'=>$receiver['name'],
                    'receiver_phone'=>$receiver['phone'],
                    'created_at'=>$this->created_at
        );
      //  return parent::toArray($request);
    }


    /**
     * @param $item
     * @return array|null
     */
    private function fetchReceiver($item)
    {
        $receiver =null;
        switch ($item->receiverable_type) {
            case User::class:
                $receiver_user= User::find($item->receiverable_id);
                $receiver= array(
                    'id'=>$receiver_user->id,
                    'type'=>'user',
                    'name'=>$receiver_user->name.' '.$receiver_user->family,
                    'phone'=>$receiver_user->phone,

                );
                break;
        }
        return $receiver;
    }
}
