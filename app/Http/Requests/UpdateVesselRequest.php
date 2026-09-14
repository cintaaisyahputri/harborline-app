<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVesselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('fleet_manager');
    }

    public function rules(): array
    {
        $vessel = $this->route('vessel');

        return [
            'name' => 'sometimes|string|max:255',
            'registration_number' => [
                'sometimes', 'string', 'max:50',
                Rule::unique('vessels', 'registration_number')->ignore($vessel),
            ],
            'home_port' => 'nullable|string|max:255',
            'captain_name' => 'nullable|string|max:255',
            'capacity_tons' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:at_sea,docked,maintenance',
            'estimated_arrival' => 'nullable|date',
        ];
    }
}
