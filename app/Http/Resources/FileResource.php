<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'path'=>$this->path,
            'title'=>$this->title,
            'created_at'=>$this->created_at,
            'jalali_created_at'=>$this->created_at
            ];
    }
}
