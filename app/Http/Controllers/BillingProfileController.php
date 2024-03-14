<?php

namespace App\Http\Controllers;

use App\Events\BillingProfileMappingChanged;
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

    private function attachVehicles(BillingProfile $billingProfile, $vehicles)
    {
        $vehicles = Arr::wrap($vehicles);

        $vehicles = Vehicle::findMany($vehicles);

        $billingProfile->vehicles()->sync($vehicles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillingProfileRequest $request)
    {
        $billing_profile = $request->user()->currentTeam->billingProfiles()->save(new BillingProfile($request->safe()->all()));

        $this->attachVehicles($billing_profile, optional($request->safe())->vehicles);

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

        $this->attachVehicles($billingProfile, optional($request->safe())->vehicles);

        return redirect()->intended(route('billing-profiles.index'));
    }
    
    public function link(Request $request)
    {
        $this->authorize('link', BillingProfile::class);

        $request->validate([
            'billingProfiles.*' => 'required|exists:billing_profiles,id',
            'vehicle' => 'required|exists:vehicles,id'
        ]);

        $vehicle = Vehicle::find($request->vehicle);
        $vehicle->billingProfiles()->sync($request->billingProfiles);

        if(count($request->billingProfiles) > 0) {
            BillingProfileMappingChanged::dispatch($vehicle);
        }
    }
    
    public function unlink(Request $request, BillingProfile $billingProfile)
    {
        $this->authorize('update', $billingProfile);

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        $billingProfile->vehicles()->detach(Vehicle::find($request->safe()->vehicle_id));
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
