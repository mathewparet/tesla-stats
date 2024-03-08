<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\BillingProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBillingProfileRequest;
use App\Http\Resources\VehicleResourceCollection;
use App\Http\Requests\UpdateBillingProfileRequest;
use App\Http\Resources\BillingProfileResourceCollection;

class BillingProfileController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(BillingProfile::class, 'billing_profile');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $billing_profiles = new BillingProfileResourceCollection($request->user()->currentTeam->billingProfiles()->paginate());

        return Inertia::render('BillingProfiles/Index', compact('billing_profiles'));
    }

    public function list(Request $request)
    {
        $this->authorize('viewAny', BillingProfile::class);

        $billing_profiles = new BillingProfileResourceCollection($request->user()->currentTeam->billingProfiles()->get());

        return back()->with('flash', ['billing_profiles' => $billing_profiles]);
    }

    private function generateTimezoneOptions()
    {
        return collect(DateTimeZone::listIdentifiers())->map(fn($tz) => [
            'label' => $tz .' - '.Carbon::now($tz)->format('d M, Y H:i:s'),
            'value' => $tz
        ])->all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $vehicles = new VehicleResourceCollection($request->user()->currentTeam->vehicles()->get());

        $timeZones = $this->generateTimezoneOptions();

        return Inertia::render('BillingProfiles/Create', compact('vehicles', 'timeZones'));
    }

    private function attachVehicles(BillingProfile $billingProfile, Request $request)
    {
        $billingProfile->vehicles()->update([
            'billing_profile_id' => null
        ]);

        $vehicles = Arr::wrap(optional($request->safe())->vehicles);

        $vehicles = Vehicle::findMany($vehicles);

        DB::transaction(function() use($vehicles, $billingProfile) {
            $vehicles->each(function($vehicle) use($billingProfile) {
                $this->authorize('update',$vehicle);
                $vehicle->update(['billing_profile_id' => $billingProfile->id]);
            });
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillingProfileRequest $request)
    {
        $billing_profile = $request->user()->currentTeam->billingProfiles()->save(new BillingProfile($request->safe()->all()));

        $this->attachVehicles($billing_profile, $request);

        return redirect()->intended(route('billing-profiles.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(BillingProfile $billingProfile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, BillingProfile $billingProfile)
    {
        $vehicles = new VehicleResourceCollection($request->user()->currentTeam->vehicles()->get());

        $billingProfile->load('vehicles');

        $editMode = true;

        $timeZones = $this->generateTimezoneOptions();

        return Inertia::render('BillingProfiles/Create', compact('billingProfile', 'editMode', 'timeZones', 'vehicles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillingProfileRequest $request, BillingProfile $billingProfile)
    {
        $billingProfile->update($request->safe()->all());

        $this->attachVehicles($billingProfile, $request);

        return redirect()->intended(route('billing-profiles.index'));
    }
    
    public function link(Request $request, BillingProfile $billingProfile)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        $vehicle = Vehicle::find($request->vehicle_id);
        
        $this->authorize('update', $vehicle);

        $vehicle->update(['billing_profile_id' => $billingProfile->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillingProfile $billingProfile)
    {
        $billingProfile->delete();

        return redirect()->intended(route('billing-profiles.index'));
    }
}
