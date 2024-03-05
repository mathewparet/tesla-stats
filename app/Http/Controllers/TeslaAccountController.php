<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\TeslaAccount;
use Illuminate\Http\Request;
use App\Contracts\TeslaAPIServiceManager;
use App\Http\Requests\LinkTessieAPIRequest;
use App\Http\Resources\TeslaAccountCollection;
use App\Http\Requests\StoreTeslaAccountRequest;
use App\Http\Requests\UpdateTeslaAccountRequest;

class TeslaAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TeslaAPIServiceManager $teslaAPIServiceManager)
    {
        $teslaAccount = $request->user()->currentTeam->teslaAccount;

        $providers = $teslaAPIServiceManager->getProviders();

        return Inertia::render('TeslaAccounts/Index', compact('teslaAccount', 'providers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function linkForm(TeslaAPIServiceManager $teslaAPIServiceManager, $provider)
    {
        $this->authorize('create', TeslaAccount::class);

        abort_if(!in_array($provider, $teslaAPIServiceManager->getProviders()), 404);

        return Inertia::render("TeslaAccounts/${provider}");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function link($provider, LinkTessieAPIRequest $request, TeslaAPIServiceManager $teslaAPIServiceManager)
    {
        abort_if(!in_array($provider, $teslaAPIServiceManager->getProviders()), 404);

        $this->authorize('create', TeslaAccount::class);

        $request->user()->currentTeam->teslaAccount()->save(new TeslaAccount([
            'provider' => $provider,
            'config' => $request->config
        ]));

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
    public function unlink($provider, Request $request, TeslaAPIServiceManager $teslaAPIServiceManager)
    {
        abort_if(!in_array($provider, $teslaAPIServiceManager->getProviders()), 404);

        $this->authorize('delete', $request->user()->currentTeam->teslaAccount);
        
        $request->user()->currentTeam->teslaAccount->delete();
    }

    public function getVehicles($provider, LinkTessieAPIRequest $request, TeslaAPIServiceManager $teslaAPIServiceManager)
    {
        $this->authorize('create', TeslaAccount::class);

        abort_if(!in_array($provider, $teslaAPIServiceManager->getProviders()), 404);

        $vehicles = $teslaAPIServiceManager
                        ->provider($provider)
                        ->useAccount($request->config)
                        ->getVehicles();

        $v = collect();

        foreach($vehicles->all() as $vehicle)
        {
            $v->push([
                'plate'  => $vehicle['plate'],
                'vin'  => $vehicle['vin'],
                'display_name' => $vehicle['last_state']['display_name']
            ]);
        }

        return back()->with('flash', ['vehicles' => $v->all()]);
    }
}
