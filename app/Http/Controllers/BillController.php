<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillResourceCollection;
use App\Http\Resources\ChargeResourceCollection;
use Vinkla\Hashids\Facades\Hashids;

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
    public function show(Bill $bill)
    {
        $this->authorize('view', $bill);

        $charges = new ChargeResourceCollection($bill->getCharges()->orderBy('id', 'desc')->get());

        return Inertia::render('Bills/View', compact('bill', 'charges'));
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
