<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blood_type' => $this->blood_type,
            'status' => $this->status,
            'urgency' => $this->urgency,
            'no_of_units' => $this->no_of_units,
            'no_of_units_donated' => $this->no_of_units_donated,
            'notes' => $this->notes,

            'distance' => $this->distance,

            'requested_at' => $this->created_at->diffForHumans(),

            'hospital' => [
                'id' => $this->hospital->id,
                'name' => $this->hospital->name,
                'address_text' => $this->hospital->address_text,
            ],

            'requester' => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
            ],
        ];
    }
}
