<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVesselRequest;
use App\Http\Requests\UpdateVesselPositionRequest;
use App\Http\Requests\UpdateVesselRequest;
use App\Http\Resources\VesselResource;
use App\Models\Vessel;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    /**
     * GET /api/fleet
     * Filters: status=at_sea|docked|maintenance, cleared_to_sail=1
     */
    public function index(Request $request)
    {
        $vessels = Vessel::query()
            ->withCount('complianceCertificates')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->boolean('expired_certificates'), fn ($q) => $q->withExpiredCertificates())
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return VesselResource::collection($vessels);
    }

    public function store(StoreVesselRequest $request)
    {
        $vessel = Vessel::create($request->validated());

        return (new VesselResource($vessel))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Vessel $vessel)
    {
        $vessel->loadCount('complianceCertificates')->load('complianceCertificates');

        return new VesselResource($vessel);
    }

    public function update(UpdateVesselRequest $request, Vessel $vessel)
    {
        $vessel->update($request->validated());

        return new VesselResource($vessel);
    }

    public function destroy(Vessel $vessel)
    {
        $vessel->delete();

        return response()->json(['message' => "Vessel {$vessel->name} removed from the fleet register."]);
    }

    /**
     * PATCH /api/fleet/{vessel}/position
     * Live position + status ping from AIS / onboard tracker.
     */
    public function updatePosition(UpdateVesselPositionRequest $request, Vessel $vessel)
    {
        $vessel->update([
            ...$request->only('current_lat', 'current_lng', 'status'),
            'last_position_at' => now(),
        ]);

        return new VesselResource($vessel);
    }
}
