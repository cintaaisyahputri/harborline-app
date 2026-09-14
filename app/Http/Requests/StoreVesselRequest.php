<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVesselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('fleet_manager');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:50|unique:vessels,registration_number',
            'home_port' => 'nullable|string|max:255',
            'captain_name' => 'nullable|string|max:255',
            'capacity_tons' => 'nullable|integer|min:0',
            'status' => 'nullable|in:at_sea,docked,maintenance',
            'estimated_arrival' => 'nullable|date',
            'current_lat' => 'nullable|numeric|between:-90,90',
            'current_lng' => 'nullable|numeric|between:-180,180',
        ];
    }
}
