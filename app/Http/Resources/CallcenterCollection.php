<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\User;
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
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'name' => $item->type == 1 ? $this->checkUser($item->sms->sender_number) : $this->checkUser($item->register->phone),
                    'province' => $item->type == 1 ? $this->getProvince($item->sms->sender_number) : $this->getProvince($item->register->phone),
                    'city' => $item->type == 1 ? $this->getCity($item->sms->sender_number) : $this->getCity($item->register->phone),
                    'phone' => $item->type == 1 ? $item->sms->sender_number : $item->register->phone,
                    'text' => $item->type == 1 ? $item->sms->body : 'ندارد',
                    'user' => $item->callcenter ? $item->callcenter->family : 'سایت',
                    'agent' => isset($item->agent) ? $item->agent->family : 'ندارد',
                    'representation' => $item->representation ? $item->representation->fa_name : 'م تهران',
                    'type' => $item->type,
                    'service' => $item->type == 1 ? $this->setService($item->sms->sender_number, $item) : $this->setService($item->register->phone, $item),
                    'date' => verta($item->created_at)->format('Y/m/d H:i:s'),
                    'date_expire' => verta($item->date_expire)->format('Y/m/d H:i:s'),
                    'status' => $item->status,
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
     * @param mixed $phone
     * @param Callcenter $item
     * @return mixed
     */
    private function setService($phone, Callcenter $item)
    {
        if ($item->request_type == "other") {
            //set last user histories
            $histories = Helper::userHistories($item->register);
            if($histories)
            {
                $last_history = $histories[0];
                return $last_history['description'];
            }
            return $item->description;
        }
        if ($item->categories()->exists() || $item->services()->exists() && !$item->factors()->exists()) {
          $servicesTitle= implode(" - ",  $item->services()->pluck('title')->toArray());
          $categoriesTitle=implode(" - ",  $item->categories()->pluck('title')->toArray());
          return $servicesTitle.' - '.$categoriesTitle;
        }

        $user = User::wherePhone($phone)->first();
        if ($user) {
            if ($item->factors->first()) {
                $temp = [];
                foreach ($item->factors as $factor) {
                    foreach ($factor->factorItems as $factor_item) {
                        if (isset($factor_item->factoritemable)) {
                            array_push($temp, $factor_item->factoritemable->title . (isset($factor_item->financialPlansType) ? ' - ' .$factor_item->financialPlansType->title : ''). (isset($factor_item->serviceItem) ? ' - ' . $factor_item->serviceItem->title : ''));
                        } elseif (isset($factor_item->category)) {
                            array_push($temp, $factor_item->category->title);
                        }
                    }
                }
                return implode(',', $temp);
            }
            if ($item->type != 1 && $user->service) {
                return $user->service->title;
            }
            else{
                return 'ندارد';
            }
        } else {
            return 'ندارد';
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
