<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\BillingProfile;
use Illuminate\Support\Carbon;
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillingProfileRequest $request)
    {
        $billing_profile = $request->user()->currentTeam->billingProfiles()->save(new BillingProfile($request->safe()->all()));

        return redirect()->intended(route('billing-profiles.edit', $billing_profile));
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
    public function edit(BillingProfile $billingProfile)
    {
        $editMode = true;

        $timeZones = $this->generateTimezoneOptions();

        return Inertia::render('BillingProfiles/Create', compact('billingProfile', 'editMode', 'timeZones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillingProfileRequest $request, BillingProfile $billingProfile)
    {
        $billingProfile->update($request->safe()->all());
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
