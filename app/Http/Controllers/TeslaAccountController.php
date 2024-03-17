<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\TeslaAccount;
use Illuminate\Http\Request;
use App\Contracts\TeslaAPIServiceManager;
use App\Http\Requests\LinkTessieAPIRequest;
use App\Jobs\SyncVehiclesForAccount;

class TeslaAccountController extends Controller
{
    public function __construct(
        private TeslaAPIServiceManager $teslaAPIServiceManager
    )
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teslaAccount = $request->user()->currentTeam->teslaAccount;

        $providers = $this->teslaAPIServiceManager->getProviders();

        $can = [
            'link' => $request->user()->can('create', TeslaAccount::class),
        ];

        return Inertia::render('TeslaAccounts/Index', compact('teslaAccount', 'providers', 'can'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function linkForm($provider)
    {
        $this->authorize('create', TeslaAccount::class);

        $this->abortIfProviderIsInvalid($provider);

        return Inertia::render("TeslaAccounts/{$provider}");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function link($provider, LinkTessieAPIRequest $request)
    {
        $this->abortIfProviderIsInvalid($provider);

        $this->authorize('create', TeslaAccount::class);

        $request->user()->currentTeam->teslaAccount()->save(new TeslaAccount([
            'provider' => $provider,
            'config' => $request->config
        ]));

        SyncVehiclesForAccount::dispatchSync($request->user()->currentTeam->teslaAccount);

        return redirect()->intended(route('tesla-accounts.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(TeslaAccount $teslaAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeslaAccount $teslaAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function unlink($provider, Request $request)
    {
        $this->abortIfProviderIsInvalid($provider);

        $this->authorize('delete', $request->user()->currentTeam->teslaAccount);
        
        $request->user()->currentTeam->teslaAccount->delete();
    }

    private function getVehiclesList($provider, $request)
    {
        return $this->teslaAPIServiceManager
                    ->provider($provider)
                    ->useAccount($request->config)
                    ->getVehicles();
    }

    private function abortIfProviderIsInvalid($provider)
    {
        abort_if(!in_array($provider, $this->teslaAPIServiceManager->getProviders()), 404);
    }

    public function getVehicles($provider, LinkTessieAPIRequest $request)
    {
        $this->authorize('create', TeslaAccount::class);

        $this->abortIfProviderIsInvalid($provider);

        $vehicles = $this->getVehiclesList($provider, $request);

        return back()->with('flash', ['vehicles' => $vehicles]);
    }
}
