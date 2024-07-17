<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundraisingRequest;
use App\Http\Requests\UpdateFundraisingRequest;
use App\Models\Category;
use App\Models\Fundraiser;
use App\Models\Fundraising;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class FundraisingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $fundraisings = $this->getFundraisingsForUser($user);

        return view('admin.fundraisings.index', compact('fundraisings'));
    }

    private function getFundraisingsForUser($user)
    {
        $fundraisingQuery = Fundraising::with(['category', 'fundraiser', 'donaturs'])->orderByDesc('id');

        if ($user->hasRole('fundraiser')) {
            $fundraisingQuery->forFundraiser($user->id);
        }

        return $fundraisingQuery->paginate(10);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $categories = Category::all();

        return view('admin.fundraisings.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundraisingRequest $request)
    {
        //   
        $fundraiser = Fundraiser::where('user_id', Auth::user()->id)->first();

        DB::transaction(function () use ($request, $fundraiser) {
            $validated = $request->validated();
    
            if ($request->hasFile("thumbnail")) {
                $thumbnailPath = $request->file("thumbnail")->store("thumbnails", "public");
                $validated['thumbnail'] = $thumbnailPath;
            }
    
            $validated['slug'] = Str::slug($validated['name']);

            $validated['fundraiser_id'] = $fundraiser->id;
            $validated['is_active'] = false;
            $validated['has_finished'] = false;

            $fundraising = Fundraising::create($validated);
            
        });
    
        return redirect()->route('admin.fundraisings.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fundraising $fundraising)
    {
        //
        $hasRequestedWithdrawal = $fundraising->withdrawals()->exists();
        $totalDonation = $fundraising->totalReachedAmount();
        $isReached = $totalDonation >= $fundraising->target_amount;
        return view('admin.fundraisings.show',compact('fundraising','totalDonation','isReached','hasRequestedWithdrawal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fundraising $fundraising)
    {
        //
        $categories = Category::all();
        return view('admin.fundraisings.edit',compact('categories','fundraising'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundraisingRequest $request, Fundraising $fundraising)
    {
        //
        DB::transaction(function () use ($request, $fundraising) {
            $validated = $request->validated();

            if($request->hasFile('thumbnail')){
                $thumbnailPath = $request->file('thumbnail')->store('thumbnail','public');
                $validated['thumbnail'] = $thumbnailPath;
            }

            $validated['slug'] = Str::slug($validated['name']);

            $fundraising->update($validated);
        });

        return redirect()->route('admin.fundraisings.show',$fundraising);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fundraising $fundraising)
    {
        //
        DB::beginTransaction();

        try {
            $fundraising->delete();
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
        }finally {
            return redirect()->route('admin.fundraisings.index');
        }
    }

    public function active_fundraising(Fundraising $fundraising){
        //
        DB::transaction(function () use ($fundraising) {
            $fundraising->update(['is_active'=> true]);
        });

        return redirect()->route('admin.fundraisings.show',$fundraising);

    }
}
