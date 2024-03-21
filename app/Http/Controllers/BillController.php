<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillResourceCollection;
use App\Http\Resources\ChargeResourceCollection;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Bill::class);

        $bills = new BillResourceCollection(Bill::whereIn('billing_profile_id', $request->user()->currentTeam->billingProfiles->pluck('id'))->orderBy('id', 'desc')->paginate());

        return Inertia::render('Bills/Index', compact('bills'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Bill $bill)
    {
        $this->authorize('view', $bill);

        $currentTeam = $request->user()->currentTeam;

        $charges = Cache::remember('bill-'.$bill->hash_id, 60, fn() => new ChargeResourceCollection($bill->getCharges()->orderBy('id', 'desc')->get()));

        $latestBills = Cache::remember('latest-bills-'.$currentTeam->hash_id, 60, fn() => Bill::whereIn('billing_profile_id', $currentTeam->billingProfiles->pluck('id'))->orderBy('id', 'desc')->take(2)->get());

        $isCurrent = $bill->is($latestBills[0]);
        $isLatest = $bill->is($latestBills[1]);

        return Inertia::render('Bills/View', compact('bill', 'charges', 'isCurrent', 'isLatest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        $this->authorize('update', $bill);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        $this->authorize('update', $bill);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        $this->authorize('delete', $bill);
    }
}
