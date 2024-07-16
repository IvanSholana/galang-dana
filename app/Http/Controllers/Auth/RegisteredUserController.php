<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'avatar' => ['required', 'image', 'mimes:png,jpg,jpeg'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Inisialisasi variabel
        $avatarPath = null;

        // Tangani upload file avatar
        try {
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['avatar' => 'Failed to upload avatar. Please try again.'])->withInput();
        }

        // Buat user baru
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'avatar' => $avatarPath,
                'password' => Hash::make($validatedData['password']),
            ]);

            // Event registered user
            event(new Registered($user));

            // Loginkan user
            Auth::login($user);

            // Redirect ke dashboard
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            // Hapus file avatar jika pembuatan user gagal
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
            return back()->withErrors(['error' => 'Failed to register user. Please try again.'])->withInput();
        }
    }
}
