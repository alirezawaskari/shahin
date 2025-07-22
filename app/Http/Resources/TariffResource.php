<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TariffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sort_by' => "created_at",
            'id' => $this->id,
            'title' => $this->title,
            'service' => $this->service->title,
            'created_at' => verta($this->created_at)->format('Y/m/d'),
        ];
    }
}
