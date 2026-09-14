<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certificate_type' => $this->certificate_type,
            'certificate_number' => $this->certificate_number,
            'issuing_authority' => $this->issuing_authority,
            'issued_at' => $this->issued_at,
            'expires_at' => $this->expires_at,
            'status' => $this->status, // valid | expiring_soon | expired
            'document_url' => $this->document_url,
            'vessel' => $this->whenLoaded('vessel', fn () => $this->vessel ? [
                'id' => $this->vessel->id,
                'name' => $this->vessel->name,
            ] : null),
            'warehouse' => $this->whenLoaded('warehouse', fn () => $this->warehouse ? [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ] : null),
        ];
    }
}
