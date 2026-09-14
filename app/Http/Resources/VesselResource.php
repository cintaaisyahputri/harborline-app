<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VesselResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'registration_number' => $this->registration_number,
            'home_port' => $this->home_port,
            'captain_name' => $this->captain_name,
            'capacity_tons' => $this->capacity_tons,
            'status' => $this->status,
            'position' => [
                'lat' => $this->current_lat,
                'lng' => $this->current_lng,
                'updated_at' => $this->last_position_at,
            ],
            'estimated_arrival' => $this->estimated_arrival,
            'cleared_to_sail' => $this->isClearedToSail(),
            'certificates_count' => $this->whenCounted('complianceCertificates'),
            'created_at' => $this->created_at,
        ];
    }
}
