<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVesselPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('fleet_manager');
    }

    public function rules(): array
    {
        return [
            'current_lat' => 'required|numeric|between:-90,90',
            'current_lng' => 'required|numeric|between:-180,180',
            'status' => 'nullable|in:at_sea,docked,maintenance',
        ];
    }
}
