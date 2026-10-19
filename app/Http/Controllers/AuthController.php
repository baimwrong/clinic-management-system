<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->status !== 'active') {
                Auth::logout();

                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact administration.']);
            }

            AuditLog::log('auth.login', "User {$user->name} logged in successfully.", null, $user);

            return redirect()->intended(route('dashboard'))
                ->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::log('auth.logout', "User {$user->name} logged out.", null, $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out securely.');
    }

    public function profile(): View
    {
        $user = Auth::user()->load(['doctor', 'patient']);

        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|max:150|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:30',
        ]);

        $user->update($validated);

        // If user is a doctor, update doctor profile details if provided
        if ($user->isDoctor() && $user->doctor) {
            $doctorData = $request->validate([
                'specialization' => 'nullable|string|max:100',
                'license_number' => "nullable|string|max:100|unique:doctors,license_number,{$user->doctor->id}",
                'qualification' => 'nullable|string|max:150',
                'room_number' => 'nullable|string|max:50',
                'bio' => 'nullable|string',
            ]);
            $user->doctor->update(array_filter($doctorData, fn ($v) => ! is_null($v)));
        }

        AuditLog::log('profile.update', 'User updated profile information.');

        return back()->with('success', 'Profile information updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLog::log('password.update', 'User changed their password.');

        return back()->with('success', 'Password updated successfully.');
    }
}
