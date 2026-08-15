<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'no_of_units_donated' => $this->no_of_units_donated,
            'donated_at' => $this->created_at->toISOString(),

            'blood_request' => [
                'id' => $this->bloodRequest->id,
                'blood_type' => $this->bloodRequest->blood_type,
                'urgency' => $this->bloodRequest->urgency,

                'hospital' => [
                    'id' => $this->bloodRequest->hospital->id,
                    'name' => $this->bloodRequest->hospital->name,
                    'address_text' => $this->bloodRequest->hospital->address_text,
                ],
            ],
        ];
    }
}
