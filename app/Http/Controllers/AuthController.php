<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Show the login and registration page.
     */
    public function showLoginRegister(): Response
    {
        return Inertia::render('Auth/LoginRegister');
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    /**
     * Authenticate an existing user.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->route('home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Create a quick guest session for easy WiFi collaboration testing.
     */
    public function guestLogin(GuestLoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $randomStr = Str::random(8);

        $user = User::create([
            'name' => $validated['name'] . ' (Guest)',
            'email' => 'guest_' . $randomStr . '@collabify.local',
            'password' => Hash::make($randomStr),
        ]);

        Auth::login($user, true);

        return redirect()->route('home');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
