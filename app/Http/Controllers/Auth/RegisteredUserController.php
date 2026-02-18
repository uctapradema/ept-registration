<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $request->validate([
            'nim' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'major' => ['required', 'string', 'max:255'],
            'faculty' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'captcha' => ['required', 'captcha'],
        ], [
            'captcha.required' => 'Kode verifikasi harus diisi.',
            'captcha.captcha' => 'Kode verifikasi tidak valid. Silakan coba lagi.',
        ]);

        $user = User::create([
            'nim' => $request->nim,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'major' => $request->major,
            'faculty' => $request->faculty,
            'password' => Hash::make($request->password),
            'role' => 'mahasiswa',
        ]);

        // Assign role mahasiswa
        $user->assignRole('mahasiswa');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('mahasiswa.dashboard', absolute: false));
    }
}
