<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillResourceCollection;
use App\Http\Resources\ChargeResourceCollection;

class BillController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bill::class, 'bill');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bills = new BillResourceCollection(Bill::whereIn('billing_profile_id', $request->user()->currentTeam->billingProfiles->pluck('id'))->orderBy('id', 'desc')->paginate());

        return Inertia::render('Bills/Index', compact('bills'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        $charges = new ChargeResourceCollection($bill->getCharges()->orderBy('id', 'desc')->get());

        return Inertia::render('Bills/View', compact('bill', 'charges'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        //
    }
}
