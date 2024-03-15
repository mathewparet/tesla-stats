<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResourceCollection;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Vehicle::class, 'vehicle');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = new VehicleResourceCollection($request->user()->currentTeam->vehicles()->latest()->with('billingProfiles')->paginate());

        return Inertia::render('Vehicles/Index', compact('vehicles'));
    }

    public function unlink(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $vehicle->update(['billing_profile_id' => null]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
    }
}
