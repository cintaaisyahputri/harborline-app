<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplianceCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('fleet_manager', 'warehouse');
    }

    public function rules(): array
    {
        return [
            'vessel_id' => 'nullable|required_without:warehouse_id|exists:vessels,id',
            'warehouse_id' => 'nullable|required_without:vessel_id|exists:warehouses,id',
            'certificate_type' => 'required|in:health_inspection,catch_origin,export_license,safety_survey',
            'certificate_number' => 'required|string|max:100|unique:compliance_certificates,certificate_number',
            'issuing_authority' => 'required|string|max:255',
            'issued_at' => 'required|date',
            'expires_at' => 'required|date|after:issued_at',
            'document_url' => 'nullable|url|max:2048',
        ];
    }
}
