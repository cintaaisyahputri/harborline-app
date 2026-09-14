<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplianceCertificateRequest;
use App\Http\Resources\ComplianceCertificateResource;
use App\Models\ComplianceCertificate;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    /**
     * GET /api/compliance
     * Filters: vessel_id, warehouse_id, certificate_type, status=expired|expiring_soon|valid
     */
    public function index(Request $request)
    {
        $certificates = ComplianceCertificate::query()
            ->with(['vessel:id,name', 'warehouse:id,name'])
            ->when($request->filled('vessel_id'), fn ($q) => $q->where('vessel_id', $request->integer('vessel_id')))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('certificate_type'), fn ($q) => $q->where('certificate_type', $request->string('certificate_type')))
            ->when($request->input('status') === 'expired', fn ($q) => $q->expired())
            ->when($request->input('status') === 'expiring_soon', fn ($q) => $q->expiringSoon())
            ->orderBy('expires_at')
            ->paginate($request->integer('per_page', 20));

        return ComplianceCertificateResource::collection($certificates);
    }

    public function store(StoreComplianceCertificateRequest $request)
    {
        $certificate = ComplianceCertificate::create($request->validated());
        $certificate->load(['vessel:id,name', 'warehouse:id,name']);

        return (new ComplianceCertificateResource($certificate))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ComplianceCertificate $certificate)
    {
        $certificate->load(['vessel:id,name', 'warehouse:id,name']);

        return new ComplianceCertificateResource($certificate);
    }

    public function update(Request $request, ComplianceCertificate $certificate)
    {
        $data = $request->validate([
            'issuing_authority' => 'sometimes|string|max:255',
            'issued_at' => 'sometimes|date',
            'expires_at' => 'sometimes|date|after:issued_at',
            'document_url' => 'nullable|url|max:2048',
        ]);

        $certificate->update($data);
        $certificate->load(['vessel:id,name', 'warehouse:id,name']);

        return new ComplianceCertificateResource($certificate);
    }

    public function destroy(ComplianceCertificate $certificate)
    {
        $certificate->delete();

        return response()->json(['message' => "Certificate {$certificate->certificate_number} removed."]);
    }
}
