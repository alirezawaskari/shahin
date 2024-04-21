<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeCollection extends ResourceCollection
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user->id,
                    'name' =>  $item->user->name.' '.$item->user->family,
                    'phone' => $item->user->phone,
                    'representation' => $item->user->admin_representation ? $item->user->admin_representation->fa_name : 'م تهران',
                    'work_title' => $item->work_title ? $item->work_title : 'ثبت نشده',
                    'status' => $item->status,
                    'show_status' => $item->show_status,
                    'role_title' => $item->user->roles->first() ? $item->user->roles->first()->display_name : 'بدون نقش',
                    'parent_fullname'=>$item->parent()->exists()?$item->parent->user->name.' '.$item->parent->user->family:'ندارد',
                    'created_at' => verta($item->created_at)->format('Y/m/d'),
                ];
            })
        ];
    }
}

