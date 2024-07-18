<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Donatur;
use App\Models\Fundraiser;
use App\Models\Fundraising;
use App\Models\FundraisingWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    //
    public function apply_fundraiser(){
        $user = Auth::user();

        DB::transaction(function () use ($user) {
            $validated['user_id'] = $user->id;
            $validated['is_active'] = false;

            Fundraiser::create($validated);
        });

        return redirect()->route('admin.fundraisers.index');
    }

    public function my_withdrawals(){   
        $user = Auth::user();
        $fundraiserId = $user->fundraiser->id;

        $withdrawals = FundraisingWithdrawal::where('fundraiser_id', $fundraiserId)->orderByDesc('id')->get();
        return view('admin.my-withdrawals.index',compact('withdrawals'));
    }

    public function my_withdrawals_details(FundraisingWithdrawal $fundraisingWithdrawal){
        return view('admin.my-withdrawals.details',compact('fundraisingWithdrawal'));
    }

    public function index()
{
    // Dapatkan user yang sedang login
    $user = Auth::user();

    // Buat query dasar untuk fundraisings dan withdrawals
    $fundraisingsQuery = Fundraising::query();
    $withdrawalsQuery = FundraisingWithdrawal::query();

    // Variabel untuk menyimpan jumlah donatur
    $donatursCount = 0;

    if ($user->hasRole('fundraiser')) {
        // Jika user adalah fundraiser, filter berdasarkan fundraiser_id
        $fundraiserId = $user->fundraiser->id;

        $fundraisingsQuery->where('fundraiser_id', $fundraiserId);
        $withdrawalsQuery->where('fundraiser_id', $fundraiserId);

        // Dapatkan ID fundraising yang terkait dengan fundraiser ini
        $fundraisingIds = $fundraisingsQuery->pluck('id');

        // Hitung jumlah donatur yang sudah membayar untuk fundraiser ini
        $donatursCount = Donatur::whereIn('fundraising_id', $fundraisingIds)
            ->where('is_paid', true)
            ->count();
    } else {
        // Jika user bukan fundraiser, hitung semua donatur yang sudah membayar
        $donatursCount = Donatur::where('is_paid', true)->count();
    }

    // Hitung jumlah fundraisings, withdrawals, categories, dan fundraisers
    $fundraisingsCount = $fundraisingsQuery->count();
    $withdrawalsCount = $withdrawalsQuery->count();
    $categoriesCount = Category::count();
    $fundraisersCount = Fundraiser::count();

    // Kirim data ke view dashboard
    return view('dashboard', [
        'fundraisings' => $fundraisingsCount,
        'withdrawals' => $withdrawalsCount,
        'categories' => $categoriesCount,
        'fundraisers' => $fundraisersCount,
        'donaturs' => $donatursCount,
    ]);
}

}
