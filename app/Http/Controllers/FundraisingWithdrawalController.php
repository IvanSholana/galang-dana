<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundraisingWithdrawalRequest;
use App\Http\Requests\UpdateFundraisingWithdrawalRequest;
use App\Models\Fundraising;
use App\Models\FundraisingWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FundraisingWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $fundraising_withdrawals = FundraisingWithdrawal::orderByDesc("id")->get();
        return view("admin.fundraising_withdrawals.index",compact("fundraising_withdrawals"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundraisingWithdrawalRequest $request, Fundraising $fundraising)
{
    $hasRequestedWithdrawal = $fundraising->withdrawals()->exists();
    if ($hasRequestedWithdrawal) {
        return redirect()->route("admin.fundraisings.show", $fundraising);
    }

    DB::transaction(function () use ($fundraising, $request) {
        $validated = $request->validated();

        $user = Auth::user();
        if (!$user->fundraiser) {
            throw new \Exception("Authenticated user is not a fundraiser");
        }

        $validated['fundraiser_id'] = $user->fundraiser->id;
        $validated['has_received'] = false;
        $validated['has_sent'] = false;
        $validated['amount_requested'] = $fundraising->totalReachedAmount();
        $validated['amount_received'] = 0;
        $validated['bank_name'] = $request->input('bank_name');
        $validated['bank_account_name'] = $request->input('bank_account_name');
        $validated['bank_account_number'] = $request->input('bank_account_number');
        // Handle proof file upload if needed
        // $validated['proof'] = $request->file('proof')->store('proofs');
        $validated['proof'] = 'proof/dummy.jpg'; // Ensure this path exists

        $fundraising->withdrawals()->create($validated);
    });

    return redirect()->route("admin.my-withdrawals", $fundraising);
}


    /**
     * Display the specified resource.
     */
    public function show(FundraisingWithdrawal $fundraisingWithdrawal)
    {
        //
        return view("admin.fundraising_withdrawals.show", compact("fundraisingWithdrawal"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FundraisingWithdrawal $fundraisingWithdrawal)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundraisingWithdrawalRequest $request, FundraisingWithdrawal $fundraisingWithdrawal)
    {
        $validated = $request->validated();

        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
            $validated['proof'] = $proofPath;
        }

        $validated['has_sent'] = 1;
        $fundraisingWithdrawal->update($validated);

        return view('admin.fundraising_withdrawals.show', compact('fundraisingWithdrawal'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundraisingWithdrawal $fundraisingWithdrawal)
    {
        //
    }
}
