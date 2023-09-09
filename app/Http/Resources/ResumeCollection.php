<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ResumeCollection extends ResourceCollection
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
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'name' =>  $item->name,
                    'phone' => $item->phone,
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'resume' => $item->resume ? $item->resume : '',
					'status' => $item->status,
                    'job_title' => isset($item->recruitment_notice) ? $item->recruitment_notice->title.' _ '.$item->recruitment_notice->province->name : 'ندارد',
                    'representation' => $item->representation ? $item->representation->fa_name : 'نمایندگی کل',
                    'per_delete' => $this->setPerDelete(),
                ];
            })
        ];
    }

    /**
     * @return mixed
     */
    private function setPerDelete()
    {
        return auth()->user()->isAbleTo('resume-delete');
    }
}
