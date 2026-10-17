<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CallcenterCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $canTransferToBusiness = auth()->user()?->hasPermission('transfer_to_business') === true;
        if ($canTransferToBusiness) {
            (new EloquentCollection($this->collection->all()))
                ->loadMissing('ghorfeOnlineLists:id');
        }

        return [
            'data' => $this->collection->map(function ($item) use ($canTransferToBusiness) {
                return [
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'name' => $item->type == 1 ? $this->checkUser($item->sms?->sender_number) : $this->checkUser($item->register?->phone),
                    'province' => $item->type == 1 ? $this->getProvince($item->sms?->sender_number) : $this->getProvince($item->register?->phone),
                    'city' => $item->type == 1 ? $this->getCity($item->sms?->sender_number) : $this->getCity($item->register?->phone),
                    'phone' => $item->type == 1 ? $item->sms?->sender_number : $item->register?->phone,
                    'text' => $item->type == 1 ? ($item->sms?->body ?? 'ندارد') : 'ندارد',
                    'user' => $item->callcenter ? $item->callcenter->family : 'سایت',
                    'agent' => isset($item->agent) ? $item->agent->family : 'ندارد',
                    'representation' => $item->representation ? $item->representation->fa_name : 'م تهران',
                    'type' => $item->type,
                    'service' => $item->type == 1 ? Callcenter::setService($item->sms?->sender_number, $item) : Callcenter::setService($item->register?->phone, $item),
                    'date' => verta($item->created_at)->format('Y/m/d H:i:s'),
                    'date_expire' => verta($item->date_expire)->format('Y/m/d H:i:s'),
                    'status' => $item->status,
                    'current_business_ids' => $canTransferToBusiness
                        ? $item->ghorfeOnlineLists->modelKeys()
                        : [],
                    'per_delete' => $this->setDeletePermission(),
                ];
            })
        ];
    }
    /**
     * @param mixed $phone
     * @return array
     */
    private function checkUser($phone)
    {
        $user = User::wherePhone($phone)->first();
        if ($user) {
            return [
                'full_name' => $user->name . ' ' . $user->family,
                'name' => $user->name,
                'family' => $user->family,
                'province_id' => $user->province_id,
                'city_id' => $user->city_id,
                'register_status' => true,
                'sex' => $user->sex,
                'vip' => $user->vip && $user->vip->type == 'VIP' ? true : false,
                'sos' => $user->vip && $user->vip->type == 'SOS' ? true : false,
                'conduct' => $user->conduct,
            ];
        } else {
            return [
                'full_name' => 'ثبت نشده',
                'name' => 'ثبت نشده',
                'family' => 'ثبت نشده',
                'province_id' => 'ثبت نشده',
                'city_id' => 'ثبت نشده',
                'register_status' => false,
                'sex' => '',
                'vip' => '',
                'sos' => '',
                'conduct' => '',
            ];
        }
    }
    /**
     * @param mixed $phone
     * @return array
     */
    private function getProvince($phone)
    {
        $user = User::wherePhone($phone)->first();
        if ($user) {
            return [
                'province' => isset($user->province) ? $user->province->name : 'ثبت نشده',
                'register_status' => true,
            ];
        } else {
            return [
                'province' => 'ثبت نشده',
                'register_status' => false
            ];
        }
    }
    /**
     * @param mixed $phone
     * @return array
     */
    private function getCity($phone)
    {
        $user = User::wherePhone($phone)->first();
        if ($user) {
            return [
                'city' => isset($user->city) ? $user->city->name : 'ثبت نشده',
                'register_status' => true,
            ];
        } else {
            return [
                'city' => 'ثبت نشده',
                'register_status' => false
            ];
        }
    }

    /**
     * @return mixed
     */
    private function setDeletePermission()
    {
        return auth()->user()->isAbleTo('callcenter-delete');
    }

    private function getConductTitle($user)
    {
        if ($user->conduct == 'challenging'){
            return 'چالش انگیز';
        }else{
            return '';
        }
    }
}
